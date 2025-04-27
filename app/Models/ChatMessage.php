<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ChatMessage extends Model implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Touch the parent ChatSession when this message is created/updated.
     */
    protected $touches = ['chatSession'];

    protected $fillable = [
        'chat_session_id',
        'user_id',
        'message',
        'sent_by',
    ];

    public function chatSession()
    {
        return $this->belongsTo(ChatSession::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new \Illuminate\Broadcasting\Channel('chat-session.' . $this->chat_session_id),
        ];
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'message.created';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->id,
            'chat_session_id' => $this->chat_session_id,
            'user_id' => $this->user_id,
            'message' => $this->message,
            'sent_by' => $this->sent_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
