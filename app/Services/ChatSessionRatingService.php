<?php

namespace App\Services;

use App\Models\ChatSession;
use App\Models\ChatMessage;
use Illuminate\Support\Arr;
use MoeMizrak\LaravelOpenrouter\Facades\LaravelOpenRouter;
use MoeMizrak\LaravelOpenrouter\DTO\MessageData;
use MoeMizrak\LaravelOpenrouter\DTO\ChatData;
use MoeMizrak\LaravelOpenrouter\Types\RoleType;

class ChatSessionRatingService
{
    /**
     * Analyze chat session and return lead score and reason.
     *
     * @param int $chatSessionId
     * @return array|null  ['score' => float, 'reason' => string]
     */
    public function analyzeLeadPotential(int $chatSessionId): ?array
    {
        // Fetch session with messages
        $chatSession = ChatSession::with(['messages' => function ($query) {
            $query->orderBy('created_at')->take(10); // limit to last 10
        }])->find($chatSessionId);

        if (!$chatSession || $chatSession->messages->isEmpty()) {
            return null;
        }

        // Prepare message context
        $contextMessages = [];

        // System prompt
        $systemPrompt = <<<PROMPT
You are an AI sales assistant. Analyze the following chat history between a user and a bot assistant, and return a JSON object with two fields:

{
  "score": a number between 0 and 1 representing the probability that this is a positive sales lead,
  "reason": a short sentence explaining why
}

Only return valid JSON. Do not explain anything else.
PROMPT;

        $contextMessages[] = new MessageData(
            role: RoleType::SYSTEM,
            content: $systemPrompt
        );

        // Map chat messages
        foreach ($chatSession->messages as $message) {
            $contextMessages[] = new MessageData(
                role: $message->is_bot ? RoleType::ASSISTANT : RoleType::USER,
                content: $message->message
            );
        }

        $chatData = new ChatData(
            messages: $contextMessages,
            model: 'openrouter/optimus-alpha'
        );


        $response = LaravelOpenRouter::chatRequest($chatData);

        $raw =  Arr::get($response->choices[0], 'message.content', '🙇‍♂️ Sorry, something went wrong.');

        // Try to decode JSON
        $parsed = json_decode($raw, true);

        if (
            is_array($parsed) &&
            isset($parsed['score']) &&
            isset($parsed['reason']) &&
            is_numeric($parsed['score']) &&
            $parsed['score'] >= 0 &&
            $parsed['score'] <= 1
        ) {
            return [
                'score' => (float) $parsed['score'],
                'reason' => $parsed['reason']
            ];
        }



        return null;
    }
}
