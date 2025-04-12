<?php

namespace App\Filament\Resources\ChatBotResource\Pages;

use App\Filament\Resources\ChatBotResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListChatBots extends ListRecords
{
    protected static string $resource = ChatBotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
