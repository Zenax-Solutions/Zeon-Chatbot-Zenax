<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatSession extends Model
{
    protected $casts = [
        'lead_score_updated_at' => 'datetime',
    ];
    protected $fillable = [
        'user_id',
        'chat_bot_id',
        'guest_ip',
        'title',
        'lead_score',
        'lead_score_updated_at',
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
    public function getLeadStatusAttribute()
    {
        if ($this->lead_score === null) {
            return 'Unknown';
        }
        return $this->lead_score >= 0.7 ? 'Positive' : 'Not Positive';
    }
}
