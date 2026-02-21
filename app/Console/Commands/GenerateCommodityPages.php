<?php

namespace App\Console\Commands;

use App\Models\PLUCode;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateCommodityPages extends Command
{
    protected $signature = 'plu:generate-commodity-pages';

    protected $description = 'Generate static commodity category pages for SEO';

    public function handle()
    {
        $this->info('Generating commodity pages...');

        $pluCodes = PLUCode::orderBy('commodity')
            ->orderBy('variety')
            ->orderBy('plu')
            ->get()
            ->groupBy('commodity');

        $baseUrl = config('app.url');
        $outputDir = storage_path('app/commodity');

        if (! is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $progressBar = $this->output->createProgressBar($pluCodes->count());
        $commodityIndex = [];

        foreach ($pluCodes as $commodity => $items) {
            $slug = Str::slug(strtolower($commodity));
            $commodityName = ucwords(strtolower($commodity));
            $count = $items->count();
            $topVarieties = $items->take(3)->pluck('variety')->implode(', ');
            $html = $this->generatePage($commodity, $commodityName, $slug, $items, $count, $topVarieties, $baseUrl);

            file_put_contents("{$outputDir}/{$slug}.html", $html);
            $commodityIndex[] = [
                'slug' => $slug,
                'name' => $commodityName,
                'count' => $count,
            ];

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();

        $this->info("Generated {$pluCodes->count()} commodity pages in {$outputDir}");

        return Command::SUCCESS;
    }

    private function generatePage($commodity, $commodityName, $slug, $items, $count, $topVarieties, $baseUrl)
    {
        $lastUpdated = now()->format('F j, Y');
        $regularCount = $count;
        $organicCount = $count; // Each PLU has an organic variant
        $totalCount = $regularCount + $organicCount;

        $directAnswer = htmlspecialchars("There are {$count} PLU codes for {$commodityName} produce items, covering varieties such as {$topVarieties}. Both regular and organic PLU codes are listed below. PLU codes are administered by the IFPS.");

        // Build BreadcrumbList schema
        $breadcrumbSchema = json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $baseUrl],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'PLU Directory', 'item' => "{$baseUrl}/plu-directory"],
                ['@type' => 'ListItem', 'position' => 3, 'name' => "{$commodityName} PLU Codes", 'item' => "{$baseUrl}/commodity/{$slug}"],
            ],
        ], JSON_UNESCAPED_SLASHES);

        // Build ItemList schema
        $itemListElements = [];
        $pos = 1;
        foreach ($items as $item) {
            $variety = htmlspecialchars($item->variety);
            $itemListElements[] = [
                '@type' => 'ListItem',
                'position' => $pos,
                'name' => "{$variety} - PLU {$item->plu}",
                'url' => "{$baseUrl}/{$item->plu}",
            ];
            $pos++;
        }
        $itemListSchema = json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => "All {$commodityName} PLU Codes",
            'description' => "Complete list of {$count} PLU codes for {$commodityName} produce items.",
            'numberOfItems' => $count,
            'itemListElement' => $itemListElements,
        ], JSON_UNESCAPED_SLASHES);

        $escapedCommodityName = htmlspecialchars($commodityName);
        $metaDescription = htmlspecialchars("Complete list of all {$count} {$commodityName} PLU codes including organic variants. Find PLU codes for {$topVarieties} and more {$commodityName} varieties.");
        $metaKeywords = htmlspecialchars("{$commodityName} PLU codes, {$commodityName} produce codes, organic {$commodityName} PLU, {$commodityName} varieties");

        $tableRows = '';
        foreach ($items as $item) {
            $variety = htmlspecialchars($item->variety);
            $size = $item->size ? htmlspecialchars($item->size) : 'Standard';
            $aka = $item->aka ? ' ('.htmlspecialchars($item->aka).')' : '';
            $tableRows .= <<<ROW
                <tr>
                    <td><a href="{$baseUrl}/{$item->plu}" class="plu-link">{$item->plu}</a></td>
                    <td><a href="{$baseUrl}/9{$item->plu}" class="organic-link">9{$item->plu}</a></td>
                    <td>{$variety}{$aka}</td>
                    <td>{$size}</td>
                </tr>
ROW;
        }

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All {$escapedCommodityName} PLU Codes - Complete List | PLU Pro</title>
    <meta name="description" content="{$metaDescription}">
    <meta name="keywords" content="{$metaKeywords}">
    <link rel="canonical" href="{$baseUrl}/commodity/{$slug}">
    <meta property="og:title" content="All {$escapedCommodityName} PLU Codes - Complete List">
    <meta property="og:description" content="{$metaDescription}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{$baseUrl}/commodity/{$slug}">
    <meta property="og:site_name" content="PLU Pro">
    <meta property="og:image" content="{$baseUrl}/icon-512.png">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="All {$escapedCommodityName} PLU Codes - Complete List">
    <meta name="twitter:description" content="{$metaDescription}">
    <script type="application/ld+json">{$breadcrumbSchema}</script>
    <script type="application/ld+json">{$itemListSchema}</script>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; margin: 0; padding: 0; background: #f9fafb; color: #1f2937; }
        .container { max-width: 1000px; margin: 0 auto; padding: 2rem 1rem; }
        .breadcrumbs { font-size: 14px; color: #6b7280; margin-bottom: 1.5rem; }
        .breadcrumbs a { color: #2563eb; text-decoration: none; }
        .breadcrumbs a:hover { text-decoration: underline; }
        .hero { background: white; border-radius: 8px; padding: 2rem; margin-bottom: 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); text-align: center; }
        h1 { color: #059669; margin: 0 0 1rem; font-size: 2rem; }
        .direct-answer { color: #4b5563; font-size: 1.1rem; line-height: 1.6; max-width: 700px; margin: 0 auto; }
        .stats { display: flex; justify-content: center; gap: 2rem; margin-top: 1.5rem; }
        .stat { text-align: center; }
        .stat-number { font-size: 1.5rem; font-weight: bold; color: #059669; }
        .stat-label { font-size: 0.875rem; color: #6b7280; }
        .table-container { background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f3f4f6; text-align: left; padding: 12px; font-size: 14px; font-weight: 600; color: #374151; border-bottom: 2px solid #e5e7eb; }
        td { padding: 10px 12px; border-bottom: 1px solid #f3f4f6; font-size: 14px; }
        tr:hover { background: #f9fafb; }
        .plu-link { color: #2563eb; text-decoration: none; font-weight: 600; }
        .plu-link:hover { text-decoration: underline; }
        .organic-link { color: #059669; text-decoration: none; font-weight: 600; }
        .organic-link:hover { text-decoration: underline; }
        .footer { text-align: center; margin-top: 2rem; padding: 1rem; color: #6b7280; font-size: 14px; }
        .footer a { color: #2563eb; text-decoration: none; }
        .footer a:hover { text-decoration: underline; }
        .source { font-size: 12px; color: #9ca3af; margin-top: 1rem; }
        .source a { color: #9ca3af; text-decoration: underline; }
        @media (max-width: 640px) {
            .stats { flex-direction: column; gap: 1rem; }
            h1 { font-size: 1.5rem; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="breadcrumbs">
            <a href="{$baseUrl}">Home</a> &rsaquo;
            <a href="{$baseUrl}/plu-directory">PLU Directory</a> &rsaquo;
            {$escapedCommodityName} PLU Codes
        </div>

        <div class="hero">
            <h1>All {$escapedCommodityName} PLU Codes</h1>
            <p class="direct-answer">{$directAnswer}</p>
            <div class="stats">
                <div class="stat">
                    <div class="stat-number">{$count}</div>
                    <div class="stat-label">PLU Codes</div>
                </div>
                <div class="stat">
                    <div class="stat-number">{$totalCount}</div>
                    <div class="stat-label">Including Organic</div>
                </div>
            </div>
        </div>

        <div class="table-container">
            <h2 style="margin: 0 0 1rem; font-size: 1.25rem;">Complete {$escapedCommodityName} PLU Code List</h2>
            <table>
                <thead>
                    <tr>
                        <th>PLU Code</th>
                        <th>Organic PLU</th>
                        <th>Variety</th>
                        <th>Size</th>
                    </tr>
                </thead>
                <tbody>
                    {$tableRows}
                </tbody>
            </table>
            <p class="source">Source: <a href="https://www.ifpsglobal.com/plu-codes" target="_blank" rel="noopener">IFPS PLU Database</a> &middot; Last updated: {$lastUpdated}</p>
        </div>

        <div class="footer">
            <p><a href="{$baseUrl}">PLU Pro Home</a> &middot; <a href="{$baseUrl}/plu-directory">PLU Directory</a> &middot; <a href="{$baseUrl}/about">About</a></p>
            <p>&copy; {$this->getYear()} PLU Pro. Your trusted produce PLU companion.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    private function getYear()
    {
        return date('Y');
    }
}
