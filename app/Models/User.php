<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, Billable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'uuid',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    protected static function booted()
    {
        static::creating(function ($user) {
            if (empty($user->uuid)) {
                $user->uuid = \Illuminate\Support\Str::uuid()->toString();
            }
        });
    }

    public function chatBots()
    {
        return $this->hasMany(\App\Models\ChatBot::class);
    }
    public function currentPlanName(): ?string
    {
        $plans = ['basic', 'standard', 'premium'];
        foreach ($plans as $plan) {
            if ($this->subscribed($plan) && !$this->subscription($plan)->ended()) {
                return $plan;
            }
        }
        return null;
    }

    public function chatbotLimit(): int
    {
        $plan = $this->currentPlanName();
        if (!$plan) {
            return 0; // no active plan, no chatbots allowed
        }
        return config("plans.$plan.chatbots", 0);
    }
}
