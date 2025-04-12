<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\ChatBot;
use Illuminate\Support\Facades\DB;

class WhatsAppWebhookController extends Controller
{
    // Webhook verification for WhatsApp (GET)
    public function verify(Request $request)
    {
        $mode = $request->input('hub_mode');
        $token = $request->input('hub_verify_token');
        $challenge = $request->input('hub_challenge');

        // Set this to a secure value and also in your WhatsApp webhook config
        $verifyToken = config('services.whatsapp.verify_token', 'my_custom_verify_token');

        if ($mode === 'subscribe' && $token === $verifyToken) {
            return response($challenge, 200);
        }

        return response('Forbidden', 403);
    }

    public function handle(Request $request)
    {
        // 1. Extract WhatsApp message and sender
        $entry = $request->input('entry', [])[0] ?? [];
        $changes = $entry['changes'][0] ?? [];
        $value = $changes['value'] ?? [];
        $messages = $value['messages'][0] ?? null;

        if (!$messages) {
            return response()->json(['status' => 'no message'], 200);
        }

        $from = $messages['from'] ?? null; // WhatsApp user phone number
        $text = $messages['text']['body'] ?? null;

        if (!$from || !$text) {
            return response()->json(['status' => 'invalid message'], 200);
        }

        // 2. Map WhatsApp user to system user and chatbot
        // TODO: Implement actual mapping logic
        // For demo: find user by phone number (assuming phone is stored in E.164 format)
        $user = User::where('phone', $from)->first();
        if (!$user) {
            // Optionally, create a new user or ignore
            return response()->json(['status' => 'user not found'], 200);
        }

        $chatbot = ChatBot::where('user_id', $user->id)->first();
        if (!$chatbot) {
            return response()->json(['status' => 'chatbot not found'], 200);
        }

        // Fetch WhatsApp integration for this chatbot
        $integration = \DB::table('whatsapp_integrations')->where('chat_bot_id', $chatbot->id)->first();
        if (!$integration) {
            return response()->json(['status' => 'whatsapp integration not found for chatbot'], 200);
        }

        // 3. Call the chatbot API internally (impersonate user)
        $token = $user->createToken('whatsapp')->plainTextToken;

        $response = Http::withToken($token)
            ->acceptJson()
            ->post(url('/api/chatbot/respond'), [
                'message' => $text,
                'chatbot_id' => $chatbot->id,
            ]);

        $reply = $response->json('reply', 'Sorry, I could not process your request.');

        // 4. Send reply back to WhatsApp user
        $whatsappAccessToken = $integration->whatsapp_token;
        $phoneNumberId = $integration->whatsapp_phone_number_id;

        if (!$whatsappAccessToken || !$phoneNumberId) {
            return response()->json(['status' => 'WhatsApp API credentials missing for this chatbot'], 500);
        }

        $sendResponse = Http::withToken($whatsappAccessToken)
            ->post("https://graph.facebook.com/v19.0/{$phoneNumberId}/messages", [
                'messaging_product' => 'whatsapp',
                'to' => $from,
                'type' => 'text',
                'text' => ['body' => $reply],
            ]);

        return response()->json(['status' => 'sent', 'whatsapp_response' => $sendResponse->json()], 200);
    }
}
