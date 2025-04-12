<?php

namespace App\Filament\Resources\BusinessDataResource\Pages;

use App\Filament\Resources\BusinessDataResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBusinessData extends CreateRecord
{
    protected static string $resource = BusinessDataResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

        // Remove chatBots from insert data
        if (isset($data['chatBots'])) {
            unset($data['chatBots']);
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $chatBots = $this->form->getState()['chatBots'] ?? [];
        $this->record->chatBots()->sync($chatBots);
    }
}
