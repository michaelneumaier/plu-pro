<?php

namespace App\Jobs;

use App\Models\UPCCode;
use App\Services\KrogerApiService;
use App\Services\KrogerApiException;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class SearchKrogerProducts implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public $tries = 3;
    public $backoff = [10, 30, 60]; // Seconds between retries

    /**
     * Create a new job instance.
     */
    public function __construct(
        private string $searchTerm
    ) {
        // Using default queue for shared hosting compatibility
    }

    /**
     * Execute the job.
     */
    public function handle(KrogerApiService $krogerApi): void
    {
        try {
            Log::info("Starting Kroger product search", ['search_term' => $this->searchTerm]);

            // Search for products using the Kroger API (limit to reduce payload)
            $products = $krogerApi->searchProducts($this->searchTerm, 10);

            if (empty($products)) {
                Log::info("No products found in Kroger search", ['search_term' => $this->searchTerm]);
                Cache::put("kroger_search_results_{$this->searchTerm}", [], 600); // Cache empty results for 10 minutes
                return;
            }

            $processedProducts = [];

            foreach ($products as $product) {
                try {
                    // Create or update UPC record if product has UPC
                    $upcCode = $product['upc'] ?? null;
                    $upcRecord = null;

                    if ($upcCode && preg_match('/^\d{12,13}$/', $upcCode)) {
                        // Extract category from Kroger categories
                        $categories = $product['categories'] ?? [];
                        $primaryCategory = !empty($categories) ? $categories[0] : 'General';
                        
                        $imageUrl = $this->extractImageUrl($product);
                        
                        $upcRecord = UPCCode::updateOrCreate(
                            ['upc' => $upcCode],
                            [
                                'name' => $product['description'] ?? '',
                                'description' => $product['description'] ?? '',
                                'brand' => $product['brand'] ?? '',
                                'category' => $primaryCategory, // Use first category or default
                                'commodity' => $primaryCategory, // Use same as category for now
                                'image_url' => $imageUrl,
                                'has_image' => false, // Will be updated after successful download
                                'kroger_categories' => $categories, // Store all categories
                                'api_data' => $product, // Store full product data for reference
                            ]
                        );

                        // Download and store image if URL exists
                        if (!empty($imageUrl)) {
                            $this->downloadImage($upcRecord, $imageUrl);
                        }

                        Log::info("Created/updated UPC record", [
                            'upc' => $upcCode,
                            'name' => $product['description'] ?? ''
                        ]);
                    }

                    // Prepare product data for cache
                    $processedProducts[] = [
                        'productId' => $product['productId'] ?? null,
                        'upc' => $upcCode,
                        'description' => $product['description'] ?? '',
                        'brand' => $product['brand'] ?? '',
                        'size' => $product['size'] ?? '',
                        'image_url' => $this->extractImageUrl($product),
                        'upc_record_id' => $upcRecord?->id,
                        'categories' => $product['categories'] ?? [],
                        'temperature' => $product['temperature'] ?? null,
                    ];

                } catch (Exception $e) {
                    Log::warning("Error processing product in search results", [
                        'product_id' => $product['productId'] ?? 'unknown',
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Cache the processed results for 10 minutes
            Cache::put("kroger_search_results_{$this->searchTerm}", $processedProducts, 600);

            Log::info("Kroger search completed successfully", [
                'search_term' => $this->searchTerm,
                'products_found' => count($processedProducts)
            ]);

        } catch (KrogerApiException $e) {
            Log::error("Kroger API error during search", [
                'search_term' => $this->searchTerm,
                'error' => $e->getMessage()
            ]);

            // Cache failure info
            Cache::put("kroger_search_failed_{$this->searchTerm}", [
                'error' => 'Kroger API error: ' . $e->getMessage(),
                'failed_at' => now()
            ], 300); // Cache for 5 minutes

        } catch (Exception $e) {
            Log::error("Unexpected error during Kroger search", [
                'search_term' => $this->searchTerm,
                'error' => $e->getMessage()
            ]);

            // Cache failure info
            Cache::put("kroger_search_failed_{$this->searchTerm}", [
                'error' => 'Search failed: ' . $e->getMessage(),
                'failed_at' => now()
            ], 300); // Cache for 5 minutes

            throw $e; // Re-throw to trigger job retry
        }
    }

    /**
     * Extract image URL from product data
     */
    private function extractImageUrl(array $product): ?string
    {
        // Check for images in the Kroger API format
        if (isset($product['images']) && is_array($product['images'])) {
            foreach ($product['images'] as $image) {
                // Look for featured front image first
                if (isset($image['featured']) && $image['featured'] && $image['perspective'] === 'front') {
                    if (isset($image['sizes']) && is_array($image['sizes'])) {
                        // Try to get medium size first, then any available size
                        foreach (['medium', 'large', 'small', 'xlarge', 'thumbnail'] as $preferredSize) {
                            foreach ($image['sizes'] as $size) {
                                if ($size['size'] === $preferredSize && !empty($size['url'])) {
                                    return $size['url'];
                                }
                            }
                        }
                    }
                }
            }
            
            // If no featured front image, try any front image
            foreach ($product['images'] as $image) {
                if ($image['perspective'] === 'front' && isset($image['sizes']) && is_array($image['sizes'])) {
                    foreach (['medium', 'large', 'small', 'xlarge', 'thumbnail'] as $preferredSize) {
                        foreach ($image['sizes'] as $size) {
                            if ($size['size'] === $preferredSize && !empty($size['url'])) {
                                return $size['url'];
                            }
                        }
                    }
                }
            }
            
            // If no front image, try any image
            foreach ($product['images'] as $image) {
                if (isset($image['sizes']) && is_array($image['sizes'])) {
                    foreach (['medium', 'large', 'small', 'xlarge', 'thumbnail'] as $preferredSize) {
                        foreach ($image['sizes'] as $size) {
                            if ($size['size'] === $preferredSize && !empty($size['url'])) {
                                return $size['url'];
                            }
                        }
                    }
                }
            }
        }

        // Fallback: Check for single image (legacy format)
        if (isset($product['image']['url']) && !empty($product['image']['url'])) {
            return $product['image']['url'];
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

                Log::info("Kroger search image downloaded successfully", [
                    'upc' => $upcCode->upc,
                    'filename' => $filename,
                    'size' => strlen($response->body())
                ]);

            } else {
                Log::warning("Failed to download Kroger search image", [
                    'upc' => $upcCode->upc,
                    'url' => $imageUrl,
                    'status' => $response->status()
                ]);
            }

        } catch (Exception $e) {
            // Log the error but don't fail the entire job
            Log::error("Kroger search image download error", [
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
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
        ];

        return $extensions[$contentType] ?? 'jpg';
    }
}