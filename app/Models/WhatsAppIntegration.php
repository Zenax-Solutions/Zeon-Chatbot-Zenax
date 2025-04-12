<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppIntegration extends Model
{
    protected $table = 'whatsapp_integrations';
    protected $guarded = [];

    public function chatBot()
    {
        return $this->belongsTo(ChatBot::class, 'chat_bot_id');
    }
}
