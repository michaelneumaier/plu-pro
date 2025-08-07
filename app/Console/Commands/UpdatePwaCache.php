<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdatePwaCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pwa:update-cache {--cache-version= : Specific version to use}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update PWA cache version to force refresh of icons and assets';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $version = $this->option('cache-version') ?: $this->generateVersion();
        
        $this->info("Updating PWA cache to version: {$version}");
        
        // Update manifest.json
        $this->updateManifest($version);
        
        // Update service worker
        $this->updateServiceWorker($version);
        
        // Update app layout
        $this->updateAppLayout($version);
        
        $this->info('✅ PWA cache updated successfully!');
        $this->info('📱 Users will need to:');
        $this->info('   1. Delete the current PWA from their device');
        $this->info('   2. Clear browser cache (or wait for service worker update)');
        $this->info('   3. Re-add the PWA to see the new icons');
        $this->info('');
        $this->info('💡 To use a specific version: php artisan pwa:update-cache --cache-version=2.0.0');
    }

    private function generateVersion(): string
    {
        return date('Y.m.d.H.i');
    }

    private function updateManifest(string $version): void
    {
        $manifestPath = public_path('manifest.json');
        $content = file_get_contents($manifestPath);
        
        // Update version
        $content = preg_replace('/"version":\s*"[^"]*"/', '"version": "' . $version . '"', $content);
        
        // Update icon URLs
        $content = preg_replace('/\/icon-(\d+)\.png\?v=[^"]*/', '/icon-$1.png?v=' . $version, $content);
        
        file_put_contents($manifestPath, $content);
        $this->info("Updated manifest.json");
    }

    private function updateServiceWorker(string $version): void
    {
        $swPath = public_path('sw.js');
        $content = file_get_contents($swPath);
        
        // Extract current version number
        preg_match('/plupro-v(\d+)/', $content, $matches);
        $currentVersion = isset($matches[1]) ? (int)$matches[1] + 1 : 4;
        
        // Update cache names - all three cache names
        $content = preg_replace('/plupro-v\d+/', 'plupro-v' . $currentVersion, $content);
        $content = preg_replace('/plupro-static-v\d+/', 'plupro-static-v' . $currentVersion, $content);
        $content = preg_replace('/plupro-dynamic-v\d+/', 'plupro-dynamic-v' . $currentVersion, $content);
        
        // Update icon URLs in STATIC_FILES
        $content = preg_replace('/\/icon-(\d+)\.png\?v=[^\']*/', '/icon-$1.png?v=' . $version, $content);
        
        file_put_contents($swPath, $content);
        $this->info("Updated service worker to cache version v{$currentVersion}");
    }

    private function updateAppLayout(string $version): void
    {
        $layoutPath = resource_path('views/layouts/app.blade.php');
        $content = file_get_contents($layoutPath);
        
        // Update manifest link
        $content = preg_replace('/\/manifest\.json\?v=[^"]*/', '/manifest.json?v=' . $version, $content);
        
        // Update icon URLs
        $content = preg_replace('/\/(favicon\.ico|icon-\d+\.png)\?v=[^"]*/', '/$1?v=' . $version, $content);
        
        file_put_contents($layoutPath, $content);
        $this->info("Updated app layout template");
    }
}
