<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
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
}
