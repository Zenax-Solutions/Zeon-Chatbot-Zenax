<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

use Illuminate\Support\Facades\Auth;
use App\Models\ChatSession;
use App\Models\ChatMessage;

class ChatWidget extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationLabel = 'Chat List';
    protected static ?string $navigationGroup = 'ChatBot';
    protected static ?string $slug = 'chat-list';
    protected static ?int $navigationSort = 99;



    protected static string $view = 'filament.pages.chat-widget';

    public $sessions;
    public $selectedSession;
    public $messages;

    public function mount()
    {
        $user = Auth::user();
        $this->sessions = ChatSession::where('user_id', $user->id)
            ->orderBy('created_at', 'asc')
            ->get();
        $sessionId = request()->query('session');
        if ($sessionId) {
            $this->selectedSession = ChatSession::where('user_id', $user->id)->find($sessionId);
        } else {
            $this->selectedSession = $this->sessions->first();
        }

        $this->messages = $this->selectedSession
            ? $this->selectedSession->messages()->orderBy('created_at')->get()
            : collect();
    }

    protected function getViewData(): array
    {
        return [
            'sessions' => $this->sessions,
            'selectedSession' => $this->selectedSession,
            'messages' => $this->messages,
        ];
    }
}
