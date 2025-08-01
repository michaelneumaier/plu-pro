<?php

namespace App\Console\Commands;

use App\Models\PLUCode;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate XML sitemap for all PLU pages';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generating sitemap for PLU pages...');

        // Get all PLU codes from database
        $pluCodes = PLUCode::orderBy('plu')->get();

        $xml = new \DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;

        // Create urlset element
        $urlset = $xml->createElement('urlset');
        $urlset->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $urlset->setAttribute('xmlns:image', 'http://www.google.com/schemas/sitemap-image/1.1');
        $xml->appendChild($urlset);

        $baseUrl = config('app.url');
        $lastmod = now()->toISOString();

        // Add homepage
        $this->addUrl($xml, $urlset, $baseUrl, $lastmod, 'daily', '1.0');
        
        // Add static pages
        $this->addUrl($xml, $urlset, "$baseUrl/about", $lastmod, 'monthly', '0.7');
        $this->addUrl($xml, $urlset, "$baseUrl/plu-directory", $lastmod, 'weekly', '0.9');

        $progressBar = $this->output->createProgressBar($pluCodes->count() * 2); // *2 for regular + organic

        foreach ($pluCodes as $pluCode) {
            // Regular PLU page
            $regularUrl = "{$baseUrl}/{$pluCode->plu}";
            $this->addUrlWithMetadata($xml, $urlset, $regularUrl, $lastmod, 'weekly', '0.8', $pluCode, false);
            $progressBar->advance();

            // Organic PLU page (9 + PLU)
            $organicUrl = "{$baseUrl}/9{$pluCode->plu}";
            $this->addUrlWithMetadata($xml, $urlset, $organicUrl, $lastmod, 'weekly', '0.8', $pluCode, true);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();

        // Save sitemap to public directory
        $sitemapContent = $xml->saveXML();
        Storage::disk('public')->put('sitemap.xml', $sitemapContent);

        // Also save to public root for easier access
        file_put_contents(public_path('sitemap.xml'), $sitemapContent);

        $totalUrls = $pluCodes->count() * 2 + 3; // +3 for homepage, about, plu-directory
        $this->info("Sitemap generated successfully with {$totalUrls} URLs!");
        $this->info('Sitemap saved to: '.public_path('sitemap.xml'));

        return Command::SUCCESS;
    }

    private function addUrl($xml, $urlset, $url, $lastmod, $changefreq, $priority)
    {
        $urlElement = $xml->createElement('url');

        $loc = $xml->createElement('loc', htmlspecialchars($url));
        $urlElement->appendChild($loc);

        $lastmodElement = $xml->createElement('lastmod', $lastmod);
        $urlElement->appendChild($lastmodElement);

        $changefreqElement = $xml->createElement('changefreq', $changefreq);
        $urlElement->appendChild($changefreqElement);

        $priorityElement = $xml->createElement('priority', $priority);
        $urlElement->appendChild($priorityElement);

        $urlset->appendChild($urlElement);
    }

    private function addUrlWithMetadata($xml, $urlset, $url, $lastmod, $changefreq, $priority, $pluCode, $isOrganic)
    {
        $urlElement = $xml->createElement('url');

        $loc = $xml->createElement('loc', htmlspecialchars($url));
        $urlElement->appendChild($loc);

        $lastmodElement = $xml->createElement('lastmod', $lastmod);
        $urlElement->appendChild($lastmodElement);

        $changefreqElement = $xml->createElement('changefreq', $changefreq);
        $urlElement->appendChild($changefreqElement);

        $priorityElement = $xml->createElement('priority', $priority);
        $urlElement->appendChild($priorityElement);

        // Add structured data for better SEO
        if ($pluCode->has_image) {
            $imageElement = $xml->createElement('image:image');
            $imageUrl = config('app.url') . '/storage/plu-images/' . $pluCode->plu . '.jpg';
            $imageLoc = $xml->createElement('image:loc', htmlspecialchars($imageUrl));
            $imageElement->appendChild($imageLoc);
            
            $imageTitle = $xml->createElement('image:title', htmlspecialchars(
                ($isOrganic ? 'Organic ' : '') . $pluCode->variety . ' - PLU ' . ($isOrganic ? '9' : '') . $pluCode->plu
            ));
            $imageElement->appendChild($imageTitle);
            
            $imageCaption = $xml->createElement('image:caption', htmlspecialchars(
                ucwords(strtolower($pluCode->commodity)) . ($pluCode->size ? ' - ' . $pluCode->size : '')
            ));
            $imageElement->appendChild($imageCaption);
            
            $urlElement->appendChild($imageElement);
        }

        $urlset->appendChild($urlElement);
    }
}
