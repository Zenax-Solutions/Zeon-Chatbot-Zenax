<?php

namespace App\Filament\Resources\ChatBotResource\Pages;

use App\Filament\Resources\ChatBotResource;
use Illuminate\Support\Facades\Auth;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateChatBot extends CreateRecord
{
    protected static string $resource = ChatBotResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();
        $currentCount = $user->chatBots()->count();
        $limit = $user->chatbotLimit();

        if ($currentCount >= $limit) {
            \Filament\Notifications\Notification::make()
                ->title('Chatbot limit reached')
                ->body("Your current plan allows maximum of {$limit} chatbots. Please upgrade your plan to create more.🥹")
                ->danger()
                ->send();

            $this->halt();
        }

        // Check for duplicate chatbot for the same website
        $existing = $user->chatBots()->where('website_url', $data['website_url'])->exists();
        if ($existing) {

            Notification::make()
                ->title('Duplicate chatbot')
                ->body('You already have a chatbot for this website.')
                ->danger()
                ->send();


            $this->halt();
        }

        $data['user_id'] = $user->id;
        return $data;
    }
    protected function afterCreate(): void
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
    }
}
