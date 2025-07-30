<?php

namespace App\Jobs;

use App\Events\UPCLookupCompleted;
use App\Events\UPCLookupFailed;
use App\Jobs\DownloadUPCImage;
use App\Models\UPCCode;
use App\Services\KrogerApiService;
use App\Services\KrogerApiException;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class LookupUPCProduct implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public $tries = 3;
    public $backoff = [10, 30, 60]; // Seconds between retries

    /**
     * Create a new job instance.
     */
    public function __construct(
        private string $upc,
        private int $userId
    ) {
        $this->onQueue('upc-lookups');
    }

    /**
     * Execute the job.
     */
    public function handle(KrogerApiService $krogerApi): void
    {
        try {
            // Check if UPC already exists in our database
            $existingUPC = UPCCode::where('upc', $this->upc)->first();
            
            if ($existingUPC) {
                // UPC already exists, broadcast success
                event(new UPCLookupCompleted($this->userId, $existingUPC));
                return;
            }

            // Validate UPC format
            if (!$krogerApi->isValidUPC($this->upc)) {
                throw new Exception("Invalid UPC format: {$this->upc}");
            }

            // Format UPC for API call
            $formattedUPC = $krogerApi->formatUPC($this->upc);

            // Call Kroger API
            $productData = $krogerApi->getProductByUPC($formattedUPC);

            if (!$productData) {
                throw new Exception("UPC {$this->upc} not found in Kroger database");
            }

            // Create UPC record with default category/commodity (will be updated by user)
            $upcCode = UPCCode::create([
                'upc' => $this->upc,
                'name' => $productData['description'] ?? 'Unknown Product',
                'description' => $productData['description'] ?? null,
                'brand' => $productData['brand'] ?? null,
                'category' => 'Fruits', // Default category - user will select proper one
                'commodity' => 'OTHER', // Default commodity - user will select proper one
                'image_url' => $this->extractImageUrl($productData),
                'kroger_categories' => $productData['categories'] ?? [],
                'api_data' => $productData,
            ]);

            // Queue image download if URL is available
            if ($upcCode->image_url) {
                DownloadUPCImage::dispatch($upcCode->id)->delay(now()->addSeconds(5));
            }

            // Broadcast success
            event(new UPCLookupCompleted($this->userId, $upcCode));

            Log::info("UPC lookup successful", [
                'upc' => $this->upc,
                'user_id' => $this->userId,
                'product_name' => $upcCode->name
            ]);

        } catch (KrogerApiException $e) {
            Log::error("Kroger API error for UPC lookup", [
                'upc' => $this->upc,
                'user_id' => $this->userId,
                'error' => $e->getMessage()
            ]);

            event(new UPCLookupFailed($this->userId, $this->upc, 'API service temporarily unavailable'));
            
            // Retry if not at max attempts
            if ($this->attempts() < $this->tries) {
                $this->release($this->backoff[$this->attempts() - 1] ?? 60);
            }

        } catch (Exception $e) {
            Log::error("UPC lookup failed", [
                'upc' => $this->upc,
                'user_id' => $this->userId,
                'error' => $e->getMessage()
            ]);

            event(new UPCLookupFailed($this->userId, $this->upc, $e->getMessage()));
        }
    }

    /**
     * Extract image URL from Kroger API response
     */
    private function extractImageUrl(array $productData): ?string
    {
        if (isset($productData['images']) && is_array($productData['images'])) {
            foreach ($productData['images'] as $image) {
                if (isset($image['url'])) {
                    return $image['url'];
                }
                // Handle nested image size structure
                if (isset($image['sizes']) && is_array($image['sizes'])) {
                    foreach ($image['sizes'] as $size) {
                        if (isset($size['url'])) {
                            return $size['url'];
                        }
                    }
                }
            }
        }

        return null;
    }

    /**
     * Handle failed job
     */
    public function failed(Exception $exception): void
    {
        Log::error("UPC lookup job failed permanently", [
            'upc' => $this->upc,
            'user_id' => $this->userId,
            'error' => $exception->getMessage()
        ]);

        event(new UPCLookupFailed($this->userId, $this->upc, 'Lookup service is currently unavailable'));
    }
}
