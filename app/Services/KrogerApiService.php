<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KrogerApiService
{
    private $clientId;
    private $clientSecret;
    private $baseUrl;

    public function __construct()
    {
        $this->clientId = config('services.kroger.client_id');
        $this->clientSecret = config('services.kroger.client_secret');
        $this->baseUrl = config('services.kroger.base_url');
    }

    /**
     * Get product information by UPC code
     *
     * @param string $upc The UPC code to lookup
     * @return array|null Product data or null if not found
     * @throws KrogerApiException
     */
    public function getProductByUPC(string $upc): ?array
    {
        try {
            $accessToken = $this->getAccessToken();
            
            // Log the search term for debugging
            Log::info("Kroger API search", [
                'search_term' => $upc,
                'url' => "{$this->baseUrl}/products?filter.term={$upc}"
            ]);
            
            $response = Http::withToken($accessToken)
                ->timeout(10)
                ->get("{$this->baseUrl}/products", [
                    'filter.term' => $upc
                ]);
            
            if ($response->successful()) {
                $data = $response->json();
                $product = $data['data'][0] ?? null;
                
                // Log the API response for debugging
                if ($product) {
                    Log::info("Kroger API found product", [
                        'search_term' => $upc,
                        'found_upc' => $product['upc'] ?? 'unknown',
                        'product_name' => $product['description'] ?? 'unknown'
                    ]);
                } else {
                    Log::warning("Kroger API no product found", [
                        'search_term' => $upc,
                        'response_data_count' => count($data['data'] ?? [])
                    ]);
                }
                
                return $product;
            }
            
            if ($response->status() === 401) {
                // Token might be expired, clear cache and retry once
                Cache::forget('kroger_access_token');
                return $this->getProductByUPC($upc);
            }
            
            Log::error('Kroger API error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'upc' => $upc
            ]);
            
            throw new KrogerApiException("API request failed with status {$response->status()}: {$response->body()}");
            
        } catch (Exception $e) {
            Log::error('Kroger API exception', [
                'message' => $e->getMessage(),
                'upc' => $upc
            ]);
            
            if ($e instanceof KrogerApiException) {
                throw $e;
            }
            
            throw new KrogerApiException("Failed to lookup UPC: {$e->getMessage()}");
        }
    }

    /**
     * Get access token with caching
     *
     * @return string
     * @throws KrogerApiException
     */
    private function getAccessToken(): string
    {
        return Cache::remember('kroger_access_token', 1700, function () {
            return $this->requestAccessToken();
        });
    }

    /**
     * Request a new access token from Kroger API
     *
     * @return string
     * @throws KrogerApiException
     */
    private function requestAccessToken(): string
    {
        try {
            $credentials = base64_encode($this->clientId . ':' . $this->clientSecret);
            
            $response = Http::asForm()
                ->withHeaders([
                    'Authorization' => 'Basic ' . $credentials,
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ])
                ->timeout(10)
                ->post("{$this->baseUrl}/connect/oauth2/token", [
                    'grant_type' => 'client_credentials',
                    'scope' => 'product.compact'
                ]);
            
            if ($response->successful()) {
                $data = $response->json();
                
                if (!isset($data['access_token'])) {
                    throw new KrogerApiException('No access token in response');
                }
                
                return $data['access_token'];
            }
            
            Log::error('Kroger OAuth error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            
            throw new KrogerApiException("OAuth request failed with status {$response->status()}: {$response->body()}");
            
        } catch (Exception $e) {
            Log::error('Kroger OAuth exception', [
                'message' => $e->getMessage()
            ]);
            
            if ($e instanceof KrogerApiException) {
                throw $e;
            }
            
            throw new KrogerApiException("Failed to obtain access token: {$e->getMessage()}");
        }
    }

    /**
     * Validate UPC format
     *
     * @param string $upc
     * @return bool
     */
    public function isValidUPC(string $upc): bool
    {
        return preg_match('/^\d{12,13}$/', trim($upc));
    }

    /**
     * Format UPC for Kroger API search
     *
     * @param string $upc
     * @return string
     */
    public function formatUPC(string $upc): string
    {
        $upc = trim($upc);
        
        // For 13-digit UPCs, remove the check digit (last digit)
        // Based on testing, this works better for Kroger API
        if (strlen($upc) === 13) {
            return substr($upc, 0, 12);
        }
        
        return $upc;
    }
}

class KrogerApiException extends Exception
{
    //
}