<?php

namespace App\Console\Commands;

use App\Models\PLUCode;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GeneratePluDirectory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plu:generate-directory';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate static PLU directory page for SEO';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generating PLU directory page...');

        // Get all PLU codes grouped by commodity
        $pluCodes = PLUCode::orderBy('commodity')
            ->orderBy('variety')
            ->orderBy('plu')
            ->get()
            ->groupBy('commodity');

        $totalItems = PLUCode::count();
        $lastUpdated = now()->format('F j, Y');

        // Generate HTML content
        $html = $this->generateHtml($pluCodes, $totalItems, $lastUpdated);

        // Save to storage and public directory
        Storage::put('plu-directory.html', $html);
        file_put_contents(public_path('plu-directory.html'), $html);

        $commodityCount = $pluCodes->count();
        $this->info('PLU directory generated successfully!');
        $this->info("- {$commodityCount} commodity groups");
        $this->info("- {$totalItems} PLU codes (each with regular and organic links)");
        $this->info('- Saved to: '.public_path('plu-directory.html'));

        return Command::SUCCESS;
    }

    private function generateHtml($pluCodesByCommidity, $totalItems, $lastUpdated)
    {
        $baseUrl = config('app.url');

        $html = <<<EOF
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete PLU Code Directory - PLUPro</title>
    <meta name="description" content="Complete searchable directory of all {$totalItems} PLU codes for produce items. Find both regular and organic PLU codes organized by commodity.">
    <meta name="keywords" content="PLU codes, produce lookup, organic PLU, fruit codes, vegetable codes, grocery PLU">
    <link rel="canonical" href="{$baseUrl}/plu-directory">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; margin: 0; padding: 20px; background: #f9fafb; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        h1 { color: #1f2937; text-align: center; margin-bottom: 1rem; }
        .subtitle { text-align: center; color: #6b7280; margin-bottom: 2rem; }
        .search-box { margin-bottom: 2rem; }
        .search-box input { width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 6px; font-size: 16px; }
        .search-box input:focus { outline: none; border-color: #10b981; }
        .commodity-group { margin-bottom: 2rem; }
        .commodity-title { color: #059669; font-size: 1.5rem; font-weight: bold; border-bottom: 2px solid #10b981; padding-bottom: 0.5rem; margin-bottom: 1rem; }
        .plu-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem; }
        .plu-item { background: #f9fafb; padding: 1rem; border-radius: 6px; border-left: 4px solid #10b981; }
        .plu-links { display: flex; gap: 1rem; margin-top: 0.5rem; }
        .plu-link { display: inline-block; padding: 6px 12px; text-decoration: none; border-radius: 4px; font-weight: 500; font-size: 14px; }
        .regular-link { background: #dbeafe; color: #1e40af; }
        .organic-link { background: #d1fae5; color: #065f46; }
        .plu-link:hover { opacity: 0.8; }
        .variety { font-weight: 600; color: #1f2937; margin-bottom: 0.25rem; }
        .details { color: #6b7280; font-size: 14px; }
        .stats { text-align: center; margin-bottom: 2rem; padding: 1rem; background: #f3f4f6; border-radius: 6px; }
        .footer { text-align: center; margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 14px; }
        @media (max-width: 768px) {
            .plu-grid { grid-template-columns: 1fr; }
            .plu-links { flex-direction: column; gap: 0.5rem; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Complete PLU Code Directory</h1>
        <p class="subtitle">Comprehensive searchable index of all produce PLU codes - both regular and organic variants</p>
        
        <div class="stats">
            <strong>{$totalItems} PLU Codes</strong> across <strong>{$pluCodesByCommidity->count()} Commodity Groups</strong>
            <br><small>Last updated: {$lastUpdated}</small>
        </div>

        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Search PLU codes, varieties, or commodities..." onkeyup="filterItems()">
        </div>

EOF;

        foreach ($pluCodesByCommidity as $commodity => $items) {
            $commodityName = ucwords(strtolower($commodity));
            $html .= "\n        <div class=\"commodity-group\" data-commodity=\"".strtolower($commodity)."\">\n";
            $html .= "            <h2 class=\"commodity-title\">{$commodityName}</h2>\n";
            $html .= "            <div class=\"plu-grid\">\n";

            foreach ($items as $pluCode) {
                $variety = htmlspecialchars($pluCode->variety);
                $size = $pluCode->size ? ' - '.htmlspecialchars($pluCode->size) : '';
                $aka = $pluCode->aka ? ' ('.htmlspecialchars($pluCode->aka).')' : '';

                $html .= '                <div class="plu-item" data-variety="'.strtolower($variety)."\" data-plu=\"{$pluCode->plu}\">\n";
                $html .= "                    <div class=\"variety\">{$variety}{$aka}</div>\n";
                $html .= "                    <div class=\"details\">{$commodityName}{$size}</div>\n";
                $html .= "                    <div class=\"plu-links\">\n";
                $html .= "                        <a href=\"{$baseUrl}/{$pluCode->plu}\" class=\"plu-link regular-link\">PLU {$pluCode->plu}</a>\n";
                $html .= "                        <a href=\"{$baseUrl}/9{$pluCode->plu}\" class=\"plu-link organic-link\">Organic 9{$pluCode->plu}</a>\n";
                $html .= "                    </div>\n";
                $html .= "                </div>\n";
            }

            $html .= "            </div>\n";
            $html .= "        </div>\n";
        }

        $html .= <<<EOF

        <div class="footer">
            <p><a href="{$baseUrl}">← Back to PLUPro Search</a></p>
            <p>PLUPro - Your trusted produce PLU code companion</p>
        </div>
    </div>

    <script>
        function filterItems() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const commodityGroups = document.querySelectorAll('.commodity-group');
            
            commodityGroups.forEach(group => {
                const items = group.querySelectorAll('.plu-item');
                let visibleItems = 0;
                
                items.forEach(item => {
                    const variety = item.dataset.variety;
                    const plu = item.dataset.plu;
                    const commodity = group.dataset.commodity;
                    const text = variety + ' ' + plu + ' ' + commodity;
                    
                    if (text.includes(searchTerm)) {
                        item.style.display = 'block';
                        visibleItems++;
                    } else {
                        item.style.display = 'none';
                    }
                });
                
                // Hide commodity group if no items are visible
                group.style.display = visibleItems > 0 ? 'block' : 'none';
            });
        }
    </script>
</body>
</html>
EOF;

        return $html;
    }
}
