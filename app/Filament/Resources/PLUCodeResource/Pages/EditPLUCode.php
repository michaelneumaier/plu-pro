<?php

namespace App\Filament\Resources\PLUCodeResource\Pages;

use App\Filament\Resources\PLUCodeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPLUCode extends EditRecord
{
    protected static string $resource = PLUCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
