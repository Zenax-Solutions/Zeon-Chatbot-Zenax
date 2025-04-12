<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Models\ChatBot;
use MoeMizrak\LaravelOpenrouter\DTO\ChatData;
use MoeMizrak\LaravelOpenrouter\DTO\MessageData;
use MoeMizrak\LaravelOpenrouter\Facades\LaravelOpenRouter;
use MoeMizrak\LaravelOpenrouter\Types\RoleType;

class ChatBotApiController extends Controller
{
    public function respond(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'chatbot_id' => 'required|integer',
        ]);

        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        // Only allow access to chatbots owned by the authenticated user
        $chatbot = ChatBot::where('id', $request->input('chatbot_id'))
            ->where('user_id', $user->id)
            ->with('businessData')
            ->first();

        if (!$chatbot) {
            return response()->json(['error' => 'Chatbot not found or not owned by user.'], 404);
        }

        $userMessage = strip_tags($request->input('message'));
        $businessInfo = $this->retrieveRelevantInfo($chatbot);

        $prompt = <<<PROMPT
                    🤖 You are Zeon, a friendly, intelligent business assistant chatbot.

                    🧠 Act like a real human assistant:
                    - Respond like you're chatting on WhatsApp — casual, friendly, and helpful.
                    - Answer step-by-step when needed. Don't overload the user with too much info at once.
                    - Use natural language. Avoid robotic or overly formal speech.
                    - If the question is unclear, kindly ask the user to clarify.
                    - Use emojis occasionally to sound friendly — but don’t overdo it.

                    🚫 Important Rules:
                    - You MUST ONLY use the information provided in the "Business Data" section below to answer the user's question.
                    - DO NOT guess, assume, or generate any information that is not explicitly stated in the data.
                    - If you cannot find a relevant answer, respond with: "🙇‍♂️ Sorry, I cannot answer that question based on our current business data."

                    🎯 WhatsApp Formatting Rules:
                    - Use plain text only — no HTML or special markup.
                    - Use line breaks and bullet points for clarity.
                    - Emphasize important words using CAPS if needed (but use sparingly).
                    - Provide direct contact info in clean format (e.g., Phone: +94XXXXXXXXX, Email: name@email.com).
                    - Add emojis for buttons like: 
                    - 📞 Call Us: +94XXXXXXXXX
                    - 💬 WhatsApp: https://wa.me/94XXXXXXXXX
                    - 🌐 Website: example.com
                    - 📧 Email: email@example.com
                    - 📍 Address: Google Maps link if available

                    📚 Business Data:
                    $businessInfo

                    🧑 User: $userMessage

                    🤖 Zeon (respond as if you're chatting on WhatsApp — human, friendly, and helpful):
                    PROMPT;


        // Build context messages (optional: accept from request, else just use current message)
        $contextMessages = [];
        if ($request->has('messages') && is_array($request->input('messages'))) {
            foreach ($request->input('messages') as $msg) {
                if (!isset($msg['type'], $msg['content'])) continue;
                if ($msg['type'] === 'sent') {
                    $contextMessages[] = new MessageData(
                        role: RoleType::USER,
                        content: strip_tags($msg['content'])
                    );
                } elseif ($msg['type'] === 'received') {
                    $contextMessages[] = new MessageData(
                        role: RoleType::ASSISTANT,
                        content: strip_tags($msg['content'])
                    );
                }
            }
        }

        // Limit context history to last 10 entries
        $historyLimit = 10;
        $contextMessages = array_slice($contextMessages, -$historyLimit);

        // Add current user question
        $contextMessages[] = new MessageData(
            role: RoleType::USER,
            content: $userMessage
        );

        // Add prompt as system message at the beginning
        array_unshift($contextMessages, new MessageData(
            role: RoleType::SYSTEM,
            content: $prompt
        ));

        $chatData = new ChatData(
            messages: $contextMessages,
            model: 'openrouter/optimus-alpha',
        );

        try {
            $response = LaravelOpenRouter::chatRequest($chatData);
            $reply = Arr::get($response->choices[0], 'message.content', '🙇‍♂️ Sorry, something went wrong.');
        } catch (\Exception $e) {
            $reply = '⚠️ Zeon is temporarily unavailable. Please try again later.';
        }

        return response()->json([
            'reply' => $reply,
        ]);
    }

    private function retrieveRelevantInfo($chatbot)
    {
        try {
            $results = $chatbot->businessData->pluck('content')->toArray() ?? [];
            if (empty($results)) {
                return '🙇‍♂️ Sorry, I cannot answer that question based on our current business data.';
            }
            return implode(" ", $results);
        } catch (\Exception $e) {
            return '⚠️ Zeon is temporarily unavailable. Please try again later.';
        }
    }
}
