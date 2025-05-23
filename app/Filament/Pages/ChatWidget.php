<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

use Illuminate\Support\Facades\Auth;
use App\Models\ChatSession;
use App\Models\ChatMessage;
use Livewire\Attributes\On;

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
    public $agentReply = '';
    public $lastMessageTimestamp;

    public function mount()
    {
        $user = Auth::user();
        $this->sessions = ChatSession::where('user_id', $user->id)
            ->orderBy('updated_at', 'desc')
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

        $this->lastMessageTimestamp = $this->messages->last()?->created_at;
    }


    protected function getViewData(): array
    {
        if ($this->selectedSession) {
            $newMessages = $this->selectedSession->messages()
                ->orderBy('created_at')
                ->where('created_at', '>', $this->lastMessageTimestamp ?? 0)
                ->get();

            if ($newMessages->count() > 0) {
                $this->messages = $this->messages->concat($newMessages);
                $this->lastMessageTimestamp = $this->messages->last()?->created_at;
            }
        }

        return [
            'sessions' => $this->sessions,
            'selectedSession' => $this->selectedSession,
            'messages' => $this->messages,
        ];
    }
    public function sendReply()
    {
        $user = Auth::user();

        $message = new ChatMessage();
        $message->chat_session_id = $this->selectedSession->id;
        $message->user_id = $user->id;
        $message->message = $this->agentReply;
        $message->sent_by = 'agent';
        $message->save();

        $this->agentReply = '';

        $this->messages = $this->selectedSession->messages()->orderBy('created_at')->get();
    }

    #[On('messageReceived')]
    public function messageReceived($event)
    {
        $newMessage = (object) $event['message'];
        $this->messages->push($newMessage);
    }
}
