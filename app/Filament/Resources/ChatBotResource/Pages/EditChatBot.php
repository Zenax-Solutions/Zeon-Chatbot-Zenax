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

    protected function afterFill(): void
    {
        $record = $this->record;
        $integration = $record->whatsappIntegration;
        if ($integration) {
            $this->form->fill([
                'whatsapp_token' => $integration->whatsapp_token,
                'whatsapp_phone_number_id' => $integration->whatsapp_phone_number_id,
                'whatsapp_verify_token' => $integration->whatsapp_verify_token,
                'webhook_url' => $integration->webhook_url,
                'status' => $integration->status,
            ]);
        }
    }

    protected function afterSave(): void
    {
        $record = $this->record;
        $data = $this->form->getState();

        // Update or create WhatsAppIntegration
        $record->whatsappIntegration()->updateOrCreate(
            [],
            [
                'whatsapp_token' => $data['whatsapp_token'] ?? null,
                'whatsapp_phone_number_id' => $data['whatsapp_phone_number_id'] ?? null,
                'whatsapp_verify_token' => $data['whatsapp_verify_token'] ?? null,
                'webhook_url' => $data['webhook_url'] ?? null,
                'status' => $data['status'] ?? null,
            ]
        );

        // Update widget code
        $record->widget_code = '<script src="' . env('APP_URL') . '/chatbot.js?website=' . $record->website_url . '&user=' . Auth::user()?->uuid . '&chatbot_id=' . $record->id . '"></script>';
        $record->save();

        Notification::make()
            ->title('Chatbot updated successfully')
            ->success()
            ->send();
    }
}
