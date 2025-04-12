<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\ChatBot;
use App\Models\WhatsAppIntegration;
use Illuminate\Support\Facades\DB;

class WhatsAppWebhookController extends Controller
{
    // Webhook verification for WhatsApp (GET)
    public function verify(Request $request)
    {
        $mode = $request->input('hub_mode');
        $token = $request->input('hub_verify_token');
        $challenge = $request->input('hub_challenge');
        $chatBotId = $request->input('chat_bot_id');

        if (!$chatBotId) {
            return response('chat_bot_id is required', 400);
        }

        $integration = WhatsAppIntegration::where('chat_bot_id', $chatBotId)->first();

        if (!$integration || !$integration->whatsapp_verify_token) {
            return response('Integration not found', 404);
        }

        $verifyToken = $integration->whatsapp_verify_token;

        if ($mode === 'subscribe' && $token === $verifyToken) {
            return response($challenge, 200);
        }

        return response('Forbidden', 403);
    }

    public function handle(Request $request)
    {
        // Log the full incoming request payload for debugging/auditing
        Log::info('WhatsApp Webhook Received', [
            'payload' => $request->all(),
        ]);

        // 1. Extract WhatsApp message and sender
        $entry = $request->input('entry', [])[0] ?? [];
        $changes = $entry['changes'][0] ?? [];
        $value = $changes['value'] ?? [];
        $messages = $value['messages'][0] ?? null;

        if (!$messages) {
            Log::warning('WhatsApp Webhook: No message found in payload', [
                'entry' => $entry,
                'changes' => $changes,
                'value' => $value,
            ]);
            return response()->json(['status' => 'no message'], 200);
        }

        $from = $messages['from'] ?? null; // WhatsApp user phone number
        $text = $messages['text']['body'] ?? null;

        if (!$from || !$text) {
            Log::warning('WhatsApp Webhook: Invalid message structure', [
                'messages' => $messages,
                'from' => $from,
                'text' => $text,
            ]);
            return response()->json(['status' => 'invalid message'], 200);
        }

        // 2. Map WhatsApp user to system user and chatbot
        // TODO: Implement actual mapping logic
        // For demo: find user by phone number (assuming phone is stored in E.164 format)
        $user = User::where('phone', $from)->first();
        if (!$user) {
            Log::warning('WhatsApp Webhook: User not found for phone', [
                'from' => $from,
            ]);
            // Optionally, create a new user or ignore
            return response()->json(['status' => 'user not found'], 200);
        }

        $chatbot = ChatBot::where('user_id', $user->id)->first();
        if (!$chatbot) {
            Log::warning('WhatsApp Webhook: Chatbot not found for user', [
                'user_id' => $user->id,
            ]);
            return response()->json(['status' => 'chatbot not found'], 200);
        }

        // Fetch WhatsApp integration for this chatbot
        $integration = DB::table('whatsapp_integrations')->where('chat_bot_id', $chatbot->id)->first();
        if (!$integration) {
            Log::warning('WhatsApp Webhook: WhatsApp integration not found for chatbot', [
                'chat_bot_id' => $chatbot->id,
            ]);
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

        Log::info('WhatsApp Webhook: Chatbot reply generated', [
            'user_id' => $user->id,
            'chatbot_id' => $chatbot->id,
            'incoming_message' => $text,
            'reply' => $reply,
        ]);

        // 4. Send reply back to WhatsApp user
        $whatsappAccessToken = $integration->whatsapp_token;
        $phoneNumberId = $integration->whatsapp_phone_number_id;

        if (!$whatsappAccessToken || !$phoneNumberId) {
            Log::error('WhatsApp Webhook: WhatsApp API credentials missing for chatbot', [
                'chat_bot_id' => $chatbot->id,
            ]);
            return response()->json(['status' => 'WhatsApp API credentials missing for this chatbot'], 500);
        }

        // Detect if reply is an image URL
        $isImage = false;
        if (is_string($reply) && preg_match('/\.(jpg|jpeg|png|gif)$/i', $reply) && filter_var($reply, FILTER_VALIDATE_URL)) {
            $isImage = true;
        }

        if ($isImage) {
            $payload = [
                'messaging_product' => 'whatsapp',
                'to' => $from,
                'type' => 'image',
                'image' => ['link' => $reply],
            ];
        } else {
            $payload = [
                'messaging_product' => 'whatsapp',
                'to' => $from,
                'type' => 'text',
                'text' => ['body' => $reply],
            ];
        }

        $sendResponse = Http::withToken($whatsappAccessToken)
            ->post("https://graph.facebook.com/v19.0/{$phoneNumberId}/messages", $payload);

        Log::info('WhatsApp Webhook: Sent reply to WhatsApp user', [
            'to' => $from,
            'reply' => $reply,
            'whatsapp_response' => $sendResponse->json(),
        ]);

        return response()->json(['status' => 'sent', 'whatsapp_response' => $sendResponse->json()], 200);
    }
}
