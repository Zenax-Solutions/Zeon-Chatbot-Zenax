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

        // Ensure WhatsAppIntegration exists
        $whatsappIntegration = $record->whatsappIntegration;
        if (!$whatsappIntegration) {
            $whatsappIntegration = new \App\Models\WhatsAppIntegration();
            $whatsappIntegration->chat_bot_id = $record->id;
        }
        $whatsappIntegration->webhook_url = env('APP_URL') . '/webhook/whatsapp' . '?chat_bot_id=' . $record->id;
        $whatsappIntegration->save();
        // Ensure WhatsAppIntegration exists

        Notification::make()
            ->title('Chatbot updated successfully')
            ->success()
            ->send();
    }
}
