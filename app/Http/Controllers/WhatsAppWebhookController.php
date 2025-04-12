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

        // 2. Find WhatsApp integration and chatbot by phone_number_id from payload
        $phoneNumberId = $value['metadata']['phone_number_id'] ?? null;
        if (!$phoneNumberId) {
            Log::warning('WhatsApp Webhook: phone_number_id not found in payload', [
                'value' => $value,
            ]);
            return response()->json(['status' => 'phone_number_id not found'], 200);
        }

        // Find WhatsApp integration by phone_number_id
        $integration = DB::table('whatsapp_integrations')->where('whatsapp_phone_number_id', $phoneNumberId)->first();
        if (!$integration) {
            Log::warning('WhatsApp Webhook: WhatsApp integration not found for phone_number_id', [
                'phone_number_id' => $phoneNumberId,
            ]);
            return response()->json(['status' => 'whatsapp integration not found for phone_number_id'], 200);
        }

        // Find associated chatbot
        $chatbot = null;
        if (isset($integration->chat_bot_id)) {
            $chatbot = ChatBot::find($integration->chat_bot_id);
        }
        if (!$chatbot) {
            Log::warning('WhatsApp Webhook: Chatbot not found for integration', [
                'chat_bot_id' => $integration->chat_bot_id ?? null,
            ]);
            return response()->json(['status' => 'chatbot not found'], 200);
        }

        // 3. Call the chatbot API internally (impersonate user)
        // Find the owner of the chatbot and create a token for internal API call
        $owner = null;
        if ($chatbot && $chatbot->user_id) {
            $owner = \App\Models\User::find($chatbot->user_id);
        }
        $token = null;
        if ($owner) {
            $token = $owner->createToken('whatsapp')->plainTextToken;
        }

        // 3. Call the chatbot API internally (as chatbot owner, if possible)
        $http = Http::acceptJson();
        if ($token) {
            $http = $http->withToken($token);
        }
        $response = $http->post(url('/api/chatbot/respond'), [
            'message' => $text,
            'chatbot_id' => $chatbot->id,
        ]);
        $reply = $response->json('reply', 'Sorry, I could not process your request.');

        Log::info('WhatsApp Webhook: Chatbot reply generated', [
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

        // Improved: Extract image URLs from reply and send as image, send text separately if needed
        $imageUrl = null;
        $textBody = $reply;

        if (is_string($reply)) {
            // Extract all URLs
            preg_match_all('/https?:\/\/[^\s]+/i', $reply, $matches);
            if (!empty($matches[0])) {
                foreach ($matches[0] as $url) {
                    if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $url)) {
                        $imageUrl = $url;
                        // Remove image URL from text body
                        $textBody = trim(str_replace($url, '', $reply));
                        break;
                    }
                }
            }
        }

        $sendResponse = null;
        // Send image if found
        if ($imageUrl) {
            $payload = [
                'messaging_product' => 'whatsapp',
                'to' => $from,
                'type' => 'image',
                'image' => ['link' => $imageUrl],
            ];
            $sendResponse = Http::withToken($whatsappAccessToken)
                ->post("https://graph.facebook.com/v19.0/{$phoneNumberId}/messages", $payload);
        }

        // Send text if present (and not just the image URL)
        if (!empty($textBody)) {
            $payload = [
                'messaging_product' => 'whatsapp',
                'to' => $from,
                'type' => 'text',
                'text' => ['body' => $textBody],
            ];
            $sendResponse = Http::withToken($whatsappAccessToken)
                ->post("https://graph.facebook.com/v19.0/{$phoneNumberId}/messages", $payload);
        }

        Log::info('WhatsApp Webhook: Sent reply to WhatsApp user', [
            'to' => $from,
            'reply' => $reply,
            'whatsapp_response' => $sendResponse->json(),
        ]);

        return response()->json(['status' => 'sent', 'whatsapp_response' => $sendResponse->json()], 200);
    }
}
