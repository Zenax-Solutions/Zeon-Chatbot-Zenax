<?php

namespace App\Filament\Resources\BusinessDataResource\Pages;

use App\Filament\Resources\BusinessDataResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBusinessData extends EditRecord
{
    protected static string $resource = BusinessDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
