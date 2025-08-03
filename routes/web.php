<?php

use App\Livewire\Dashboard;
use App\Livewire\Lists\Create as ListsCreate;
use App\Livewire\Lists\Edit as ListsEdit;
use App\Livewire\Lists\Index as ListsIndex;
use App\Livewire\Lists\SharedView;
use App\Livewire\Lists\Show as ListsShow;
use App\Livewire\Marketplace\Browse as MarketplaceBrowse;
use App\Livewire\Marketplace\ViewList as MarketplaceViewList;
use App\Livewire\SearchPLUCode;
use App\Http\Controllers\Auth\GoogleController;
use Illuminate\Support\Facades\Route;

Route::get('/', SearchPLUCode::class)->name('home');

// PWA start route - smart client-side auth detection
Route::get('/pwa', function () {
    return view('pwa-landing');
})->name('pwa.start');

// API endpoint for PWA auth check
Route::get('/pwa/auth-check', function () {
    return response()->json([
        'authenticated' => auth()->check(),
        'verified' => auth()->check() && auth()->user()->hasVerifiedEmail(),
        'user_id' => auth()->id()
    ]);
})->name('pwa.auth-check');

// About page
Route::get('/about', \App\Livewire\About::class)->name('about');

// PLU Directory - static HTML page for SEO
Route::get('/plu-directory', function () {
    $filePath = public_path('plu-directory.html');
    if (file_exists($filePath)) {
        return response()->file($filePath);
    }
    abort(404, 'PLU directory not generated yet. Run: php artisan plu:generate-directory');
})->name('plu.directory');

// Google OAuth routes
Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('auth.google.callback');

// PLU Pages - handles both regular (3000-5000) and organic (93000-95000) PLUs
Route::get('/{plu}', \App\Livewire\PLUPage::class)
    ->where('plu', '^(9[3-5][0-9]{3}|[3-5][0-9]{3})$')
    ->name('plu.show');

// Public shared list route (no auth required)
Route::get('/list/{shareCode}', SharedView::class)->name('lists.shared');

// Marketplace routes (public - no auth required)
Route::get('/marketplace', MarketplaceBrowse::class)->name('marketplace.browse');
Route::get('/marketplace/{shareCode}', MarketplaceViewList::class)->name('marketplace.view');

Route::middleware(['auth'])->group(function () {
    Route::get('/lists', ListsIndex::class)->name('lists.index');
    Route::get('/lists/create', ListsCreate::class)->name('lists.create');
    Route::get('/lists/{userList}', ListsShow::class)->name('lists.show');
    Route::get('/lists/{userList}/edit', ListsEdit::class)->name('lists.edit');

    // Google OAuth unlink route
    Route::delete('/auth/google/unlink', [GoogleController::class, 'unlink'])->name('auth.google.unlink');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
});
