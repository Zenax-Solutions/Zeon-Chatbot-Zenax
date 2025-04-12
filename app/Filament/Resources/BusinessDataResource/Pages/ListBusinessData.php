<?php

namespace App\Filament\Resources\BusinessDataResource\Pages;

use App\Filament\Resources\BusinessDataResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBusinessData extends ListRecords
{
    protected static string $resource = BusinessDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
