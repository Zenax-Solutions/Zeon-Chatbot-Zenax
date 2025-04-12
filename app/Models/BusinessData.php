<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessData extends Model
{
    protected $guarded = [];

    public function chatBots()
    {
        return $this->belongsToMany(ChatBot::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
