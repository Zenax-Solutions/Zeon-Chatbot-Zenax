<?php

namespace App\Filament\Resources\ChatBotResource\Pages;

use App\Filament\Resources\ChatBotResource;
use Illuminate\Support\Facades\Auth;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditChatBot extends EditRecord
{
    protected static string $resource = ChatBotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    protected function afterSave(): void
    {
        $record = $this->record;
        $record->widget_code = '<script src="' . env('APP_URL') . '/chatbot.js?website=' . $record->website_url . '&user=' . Auth::user()?->uuid . '&chatbot_id=' . $record->id . '"></script>';
        $record->save();

        Notification::make()
            ->title('Chatbot updated successfully')
            ->success()
            ->send();
    }
}
