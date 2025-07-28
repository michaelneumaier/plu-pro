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
use Illuminate\Support\Facades\Route;

Route::get('/', SearchPLUCode::class)->name('home');

// About page
Route::get('/about', \App\Livewire\About::class)->name('about');

// PLU Pages - handles both regular (3000-5000) and organic (93000-95000) PLUs
Route::get('/{plu}', \App\Livewire\PLUPage::class)
    ->where('plu', '^(9[3-5][0-9]{3}|[3-5][0-9]{3})$')
    ->name('plu.show');

// Public shared list route (no auth required)
Route::get('/list/{shareCode}', SharedView::class)->name('lists.shared');

Route::middleware(['auth'])->group(function () {
    Route::get('/lists', ListsIndex::class)->name('lists.index');
    Route::get('/lists/create', ListsCreate::class)->name('lists.create');
    Route::get('/lists/{userList}', ListsShow::class)->name('lists.show');
    Route::get('/lists/{userList}/edit', ListsEdit::class)->name('lists.edit');

    // Marketplace routes
    Route::get('/marketplace', MarketplaceBrowse::class)->name('marketplace.browse');
    Route::get('/marketplace/{shareCode}', MarketplaceViewList::class)->name('marketplace.view');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
});
