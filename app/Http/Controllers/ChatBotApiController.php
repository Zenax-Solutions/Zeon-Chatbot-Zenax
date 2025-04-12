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
- Engage with the user like a thoughtful human would.
- Respond step-by-step, not everything in a single message — provide only the most relevant part first, and follow up naturally if needed.
- Use casual, friendly, and professional language.
- Avoid robotic or overly formal speech.
- If the question is unclear, politely ask for clarification.
- Occasionally use emojis to sound friendly and human, but don't overdo it.

🚫 Important Rules:
- You MUST ONLY use the information provided in the "Business Data" section below to answer the user's question.
- DO NOT guess, assume, or generate any information that is not explicitly stated in the data.
- If you cannot find relevant information in the data, reply with: "🙇‍♂️ Sorry, I cannot answer that question based on our current business data."

🎨 Formatting Rules:
- Return a clean, readable  using Tailwind CSS with well spaces.
- Use <p> for paragraphs, <ul>/<li> for lists.
- Convert only:
  - phone numbers to "tel:" links with a "📞 Call Us" button
  - WhatsApp numbers to "https://wa.me/" links with a "💬 WhatsApp" button with onean a new tab
  - emails to "mailto:" links with an "📧 Email Us" button
  - website URLs to buttons labeled "🌐 Visit Website"
  - address to "https://www.google.com/maps/search/?api=1&query=" links with a "📍 View on Map" button
  - Use <a> tags for links, and ensure they open in a new tab.
  - images should be wrapped in <figure> tags with <figcaption> for captions.
  - image card should be wrapped in <div> tags with class "image-card" and contain a <p> tag for the caption.
  - audio should be wrapped in <audio> tags with controls.
  - audio should be wrapped in <div> tags with class "audio-card" and contain a <p> tag for the caption.
- DO NOT nest <a> tags inside another <a>
- DO NOT use double quotes inside attributes
- Do not overuse divs — keep structure minimal and clean
- Never output broken or invalid HTML
- Do not use <script> tags or any JavaScript
- Do not use <style> tags or any CSS
- Do not use <head> or <body> tags
- Do not use <html> tags
- Do not use <meta> tags
- Do not use <link> tags
- Do not use <title> tags
- Do not use <svg> tags

📚 Business Data:
$businessInfo

🧑 User: $userMessage

🤖 Zeon (respond like a real human using valid HTML, and continue the conversation naturally):
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
