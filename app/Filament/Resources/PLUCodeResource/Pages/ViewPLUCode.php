<?php

namespace App\Filament\Resources\PLUCodeResource\Pages;

use App\Filament\Resources\PLUCodeResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPLUCode extends ViewRecord
{
    protected static string $resource = PLUCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}