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
        $xml->appendChild($urlset);

        $baseUrl = config('app.url');
        $lastmod = now()->toISOString();

        // Add homepage
        $this->addUrl($xml, $urlset, $baseUrl, $lastmod, 'daily', '1.0');

        $progressBar = $this->output->createProgressBar($pluCodes->count() * 2); // *2 for regular + organic

        foreach ($pluCodes as $pluCode) {
            // Regular PLU page
            $regularUrl = "{$baseUrl}/{$pluCode->plu}";
            $this->addUrl($xml, $urlset, $regularUrl, $lastmod, 'weekly', '0.8');
            $progressBar->advance();

            // Organic PLU page (9 + PLU)
            $organicUrl = "{$baseUrl}/9{$pluCode->plu}";
            $this->addUrl($xml, $urlset, $organicUrl, $lastmod, 'weekly', '0.8');
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();

        // Save sitemap to public directory
        $sitemapContent = $xml->saveXML();
        Storage::disk('public')->put('sitemap.xml', $sitemapContent);

        // Also save to public root for easier access
        file_put_contents(public_path('sitemap.xml'), $sitemapContent);

        $totalUrls = $pluCodes->count() * 2 + 1; // +1 for homepage
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
}
