<?php

namespace App\Jobs;

use App\Events\UPCLookupCompleted;
use App\Events\UPCLookupFailed;
use App\Models\UPCCode;
use App\Services\KrogerApiService;
use App\Services\KrogerApiException;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
        // Using default queue for shared hosting compatibility
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

            // Call Kroger API (getProductByUPC handles formatting internally)
            $productData = $krogerApi->getProductByUPC($this->upc);

            if (!$productData) {
                // UPC not found is a normal business case, not an error
                Log::info("UPC not found in Kroger database", [
                    'upc' => $this->upc,
                    'user_id' => $this->userId
                ]);
                
                // Store failure in cache for polling to detect
                Cache::put("upc_lookup_failed_{$this->upc}", [
                    'message' => 'Product not found',
                    'timestamp' => now()
                ], 300); // Keep for 5 minutes
                
                event(new UPCLookupFailed($this->userId, $this->upc, 'Product not found'));
                return;
            }

            // Extract image URL from product data
            $imageUrl = $this->extractImageUrl($productData);
            
            // Create UPC record with default category/commodity (will be updated by user)
            $upcCode = UPCCode::create([
                'upc' => $this->upc,
                'name' => $productData['description'] ?? 'Unknown Product',
                'description' => $productData['description'] ?? null,
                'brand' => $productData['brand'] ?? null,
                'category' => 'Fruits', // Default category - user will select proper one
                'commodity' => 'OTHER', // Default commodity - user will select proper one
                'image_url' => $imageUrl,
                'kroger_categories' => $productData['categories'] ?? [],
                'api_data' => $productData,
                'has_image' => false, // Will be updated after successful download
            ]);

            // Download image immediately if URL is available
            if ($imageUrl) {
                $this->downloadImage($upcCode, $imageUrl);
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

            // Store failure in cache if this is the last attempt
            if ($this->attempts() >= $this->tries) {
                Cache::put("upc_lookup_failed_{$this->upc}", [
                    'message' => 'Service temporarily unavailable',
                    'timestamp' => now()
                ], 300);
            }

            event(new UPCLookupFailed($this->userId, $this->upc, 'Service temporarily unavailable'));
            
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

            // Store failure in cache
            Cache::put("upc_lookup_failed_{$this->upc}", [
                'message' => 'An error occurred while looking up this product',
                'timestamp' => now()
            ], 300);

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
     * Download and save product image
     */
    private function downloadImage(UPCCode $upcCode, string $imageUrl): void
    {
        try {
            // Download image from Kroger
            $response = Http::timeout(30)->get($imageUrl);

            if ($response->successful()) {
                // Determine file extension from content type or URL
                $contentType = $response->header('Content-Type', 'image/jpeg');
                $extension = $this->getExtensionFromContentType($contentType);
                
                $filename = "{$upcCode->upc}.{$extension}";
                $path = "upc_images/{$filename}";
                
                // Create directory if it doesn't exist  
                Storage::disk('public')->makeDirectory('upc_images');
                
                // Store the image
                Storage::disk('public')->put($path, $response->body());

                // Update UPC record
                $upcCode->update(['has_image' => true]);

                Log::info("UPC image downloaded successfully", [
                    'upc' => $upcCode->upc,
                    'filename' => $filename,
                    'size' => strlen($response->body())
                ]);

            } else {
                Log::warning("Failed to download UPC image", [
                    'upc' => $upcCode->upc,
                    'url' => $imageUrl,
                    'status' => $response->status()
                ]);
            }

        } catch (Exception $e) {
            // Log the error but don't fail the entire job
            Log::error("UPC image download error", [
                'upc' => $upcCode->upc,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get file extension from content type
     */
    private function getExtensionFromContentType(string $contentType): string
    {
        return match ($contentType) {
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            default => 'jpg',
        };
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
