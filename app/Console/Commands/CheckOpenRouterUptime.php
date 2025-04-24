<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Models\User;
use MoeMizrak\LaravelOpenrouter\Facades\LaravelOpenRouter;
use MoeMizrak\LaravelOpenrouter\DTO\MessageData;
use MoeMizrak\LaravelOpenrouter\DTO\ChatData;
use MoeMizrak\LaravelOpenrouter\Types\RoleType;
use App\Notifications\OpenRouterDownNotification;
use Exception;

class CheckOpenRouterUptime extends Command
{
    protected $signature = 'openrouter:check-uptime';
    protected $description = 'Check OpenRouter API uptime and notify admin if down';

    public function handle()
    {
        try {
            $messageData = new MessageData(
                role: RoleType::USER,
                content: 'ping'
            );
            $chatData = new ChatData(
                messages: [$messageData],
                model: 'google/gemini-2.0-flash-exp:free'
            );
            $response = LaravelOpenRouter::chatRequest($chatData);

            if (!isset($response->choices[0]['message']['content'])) {
                throw new Exception('No valid response from OpenRouter');
            }

            $this->info('OpenRouter API is UP.');
        } catch (\Throwable $e) {
            Log::error('OpenRouter API is DOWN: ' . $e->getMessage());

            // Notify all admins (assuming is_admin column on users table)
            $admins = User::where('is_admin', true)->get();
            if ($admins->count() > 0) {
                Notification::send($admins, new OpenRouterDownNotification($e->getMessage()));
            }

            $this->error('OpenRouter API is DOWN. Admins notified.');
        }
    }
}
