<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatBot extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function businessData()
    {
        return $this->belongsToMany(BusinessData::class);
    }
    public function whatsappIntegration()
    {
        return $this->hasOne(WhatsAppIntegration::class, 'chat_bot_id');
    }
}
