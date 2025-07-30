<?php

namespace App\Jobs;

use App\Models\UPCCode;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DownloadUPCImage implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public $tries = 3;
    public $backoff = [30, 60, 120];

    /**
     * Create a new job instance.
     */
    public function __construct(
        private int $upcCodeId
    ) {
        $this->onQueue('image-downloads');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $upcCode = UPCCode::find($this->upcCodeId);
            
            if (!$upcCode || !$upcCode->image_url) {
                Log::warning("UPC image download skipped", [
                    'upc_code_id' => $this->upcCodeId,
                    'reason' => $upcCode ? 'no_image_url' : 'upc_not_found'
                ]);
                return;
            }

            // Download image from Kroger
            $response = Http::timeout(30)->get($upcCode->image_url);

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
                    'url' => $upcCode->image_url,
                    'status' => $response->status()
                ]);
            }

        } catch (Exception $e) {
            Log::error("UPC image download error", [
                'upc_code_id' => $this->upcCodeId,
                'error' => $e->getMessage()
            ]);

            // Retry if not at max attempts
            if ($this->attempts() < $this->tries) {
                $this->release($this->backoff[$this->attempts() - 1] ?? 120);
            }
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
        Log::error("UPC image download job failed permanently", [
            'upc_code_id' => $this->upcCodeId,
            'error' => $exception->getMessage()
        ]);
    }
}
