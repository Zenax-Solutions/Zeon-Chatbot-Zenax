<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\WhatsAppIntegration;

class RefreshWhatsAppTokens extends Command
{
    protected $signature = 'whatsapp:refresh-tokens';
    protected $description = 'Refresh WhatsApp API tokens for all integrations that are expiring soon';

    public function handle()
    {
        $now = Carbon::now();
        $soon = $now->copy()->addMinutes(30);

        $integrations = WhatsAppIntegration::whereNotNull('refresh_token')
            ->whereNotNull('token_expires_at')
            ->where('token_expires_at', '<', $soon)
            ->get();

        foreach ($integrations as $integration) {
            $this->info("Refreshing token for integration ID {$integration->id} (chat_bot_id: {$integration->chat_bot_id})");

            // Meta OAuth token refresh endpoint and params
            $clientId = config('services.whatsapp.client_id');
            $clientSecret = config('services.whatsapp.client_secret');
            $refreshToken = $integration->refresh_token;

            $response = Http::asForm()->post('https://graph.facebook.com/v19.0/oauth/access_token', [
                'grant_type' => 'fb_exchange_token',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'fb_exchange_token' => $refreshToken,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $integration->whatsapp_token = $data['access_token'];
                $integration->refresh_token = $data['refresh_token'] ?? $refreshToken;
                $integration->token_expires_at = $now->copy()->addSeconds($data['expires_in'] ?? 3600);
                $integration->save();

                $this->info("Token refreshed for integration ID {$integration->id}");
                Log::info('WhatsApp token refreshed', ['integration_id' => $integration->id]);
            } else {
                $this->error("Failed to refresh token for integration ID {$integration->id}");
                Log::error('Failed to refresh WhatsApp token', [
                    'integration_id' => $integration->id,
                    'response' => $response->body(),
                ]);
                // Notify the owner of the expired chatbot integration
                $chatbot = \App\Models\ChatBot::find($integration->chat_bot_id);
                if ($chatbot && $chatbot->user_id) {
                    $owner = \App\Models\User::find($chatbot->user_id);
                    dd($owner);
                    if ($owner) {
                        $owner->notify(new \App\Notifications\WhatsAppTokenRefreshFailed($integration->id, $response->body()));
                    }
                }
            }
        }

        $this->info('Token refresh process completed.');
    }
}
