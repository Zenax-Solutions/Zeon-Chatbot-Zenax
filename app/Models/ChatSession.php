<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatSession extends Model
{
    protected $fillable = [
        'user_id',
        'chat_bot_id',
        'title',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function chatBot()
    {
        return $this->belongsTo(ChatBot::class);
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }
}
