<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatSession extends Model
{
    protected $fillable = [
        'user_id',
        'chat_bot_id',
        'guest_ip',
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
    public function getLeadStatusAttribute()
    {
        $cacheKey = 'lead_status_' . $this->id;

        return cache()->remember($cacheKey, 300, function () {
            $service = app(\App\Services\ChatSessionRatingService::class);
            $result = $service->analyzeLeadPotential($this->id); // updated method name

            dd($result);

            if (!$result || !isset($result['score'])) {
                return 'Unknown';
            }

            return $result['score'] >= 0.7 ? 'Positive' : 'Not Positive';
        });
    }
}
