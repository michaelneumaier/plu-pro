<?php

namespace App\Jobs;

use App\Models\PLUCode;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class DownloadPLUImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $plu;

    public function __construct($plu)
    {
        $this->plu = $plu;
    }

    public function handle()
    {
        $baseUrl = 'https://www.kroger.com/product/images/large/front/';
        $paddedPLU = str_pad($this->plu, 13, '0', STR_PAD_LEFT);
        $imageUrl = $baseUrl.$paddedPLU;

        try {
            $response = Http::get($imageUrl);

            if ($response->successful()) {
                Storage::put("public/product_images/{$this->plu}.jpg", $response->body());

                PLUCode::where('plu', $this->plu)->update([
                    'has_image' => true,
                ]);
            }
        } catch (\Exception $e) {
            \Log::error("Failed to download image for PLU {$this->plu}: ".$e->getMessage());
        }
    }
}
