<?php

namespace App\Livewire;

use App\Models\PLUCode;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class ImageDownloader extends Component
{
    public $isProcessing = false;

    public $processedPLUs = 0;

    public $currentPLU = '';

    public $successCount = 0;

    public $failureCount = 0;

    public $currentChunk = 0;

    public $completedChunks = [];

    public $totalSuccess = 0;

    public $totalFailure = 0;

    public function getChunkSizeProperty()
    {
        return ceil(PLUCode::count() / 50);
    }

    public function startDownload($startChunk = 1)
    {
        set_time_limit(0);

        $totalChunks = 50;
        $this->currentChunk = $startChunk;

        while ($this->currentChunk <= $totalChunks) {
            $this->processChunk($this->currentChunk);
            $this->currentChunk++;
        }

        session()->flash('message', "All chunks complete! Total Success: {$this->totalSuccess}, Total Failed: {$this->totalFailure}");
    }

    protected function processChunk($chunkNumber)
    {
        $this->isProcessing = true;
        $this->processedPLUs = 0;
        $this->successCount = 0;
        $this->failureCount = 0;

        $offset = ($chunkNumber - 1) * $this->chunkSize;

        $pluCodes = PLUCode::select('plu')
            ->orderBy('id')
            ->skip($offset)
            ->take($this->chunkSize)
            ->get();

        foreach ($pluCodes as $pluCode) {
            $this->currentPLU = $pluCode->plu;
            $this->downloadSingleImage($pluCode->plu);
            $this->processedPLUs++;
        }

        $this->totalSuccess += $this->successCount;
        $this->totalFailure += $this->failureCount;

        $this->completedChunks[$chunkNumber] = true;
    }

    protected function downloadSingleImage($plu)
    {
        $baseUrl = 'https://www.kroger.com/product/images/large/front/';
        $paddedPLU = str_pad($plu, 13, '0', STR_PAD_LEFT);
        $imageUrl = $baseUrl.$paddedPLU;

        try {
            $response = Http::timeout(5)->get($imageUrl);

            if (
                $response->successful() &&
                in_array($response->header('content-type'), ['image/jpeg', 'image/png'])
            ) {

                $extension = $response->header('content-type') === 'image/png' ? 'png' : 'jpg';
                $path = "product_images/{$plu}.{$extension}";

                try {
                    $saved = Storage::disk('public')->put($path, $response->body());

                    if ($saved && Storage::disk('public')->exists($path)) {
                        PLUCode::where('plu', $plu)->update(['has_image' => true]);
                        $this->successCount++;
                    } else {
                        $this->failureCount++;
                    }
                } catch (\Exception $e) {
                    $this->failureCount++;
                    \Log::error("Failed to save image for PLU {$plu}: ".$e->getMessage());
                }
            } else {
                $this->failureCount++;
            }
        } catch (\Exception $e) {
            $this->failureCount++;
            \Log::error("Failed to download image for PLU {$plu}: ".$e->getMessage());
        }
    }

    public function getProgressProperty()
    {
        return $this->chunkSize > 0 ? ($this->processedPLUs / $this->chunkSize) * 100 : 0;
    }

    public function getTotalProgressProperty()
    {
        return ($this->currentChunk - 1) * 2; // Each chunk represents 2% of total progress
    }

    public function render()
    {
        return view('livewire.image-downloader', [
            'totalChunks' => 50,
            'totalPLUs' => PLUCode::count(),
        ]);
    }
}
