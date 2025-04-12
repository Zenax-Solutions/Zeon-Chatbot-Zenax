<?php

namespace App\Filament\Resources\BusinessDataResource\Pages;

use App\Models\ChatBot;

use App\Filament\Resources\BusinessDataResource;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Forms\Components\Placeholder;
use Filament\Actions;

class CreateBusinessData extends CreateRecord
{
    protected static string $resource = BusinessDataResource::class;
    protected function shouldRenderForm(): bool
    {
        // Hide the form if the user has no chatbots
        return ChatBot::where('user_id', auth()->user()?->id)->exists();
    }
    protected function getFormSchema(): array
    {
        if (!ChatBot::where('user_id', auth()->user()?->id)->exists()) {
            return [
                \Filament\Forms\Components\Placeholder::make('no_chatbots')
                    ->content('No chatbots found. Please create one to assign business data.')
                    ->extraAttributes(['class' => 'text-center text-lg font-semibold p-4 bg-yellow-50 border border-yellow-300 rounded'])
                    ->hintAction(
                        \Filament\Forms\Components\Actions\Action::make('createChatBot')
                            ->label('Add a ChatBot')
                            ->url('/admin/chat-bots/create')
                            ->openUrlInNewTab()
                            ->button()
                            ->color('primary')
                    ),
            ];
        }
        return parent::getFormSchema();
    }
    protected function getFormActions(): array
    {
        if (!ChatBot::where('user_id', auth()->user()?->id)->exists()) {
            return [];
        }
        return parent::getFormActions();
    }
}
