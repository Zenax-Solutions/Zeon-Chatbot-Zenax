<?php

use App\Models\BusinessData;
use App\Models\ChatSession;
use App\Models\ChatMessage;
use Illuminate\Support\Arr;
use Livewire\Volt\Component;
use MoeMizrak\LaravelOpenrouter\DTO\ChatData;
use MoeMizrak\LaravelOpenrouter\DTO\MessageData;
use MoeMizrak\LaravelOpenrouter\Facades\LaravelOpenRouter;
use MoeMizrak\LaravelOpenrouter\Types\RoleType;
use Livewire\Attributes\On;

new class extends Component
{
    public $message = '';
    public $messages = [];

    public $chatbot = null;
    public $user = null;
    public $website = null;

    public $placeholder = false;
    public $sessionId = null;
    public $guestIp = null;
    public $handoverActive = false;

    public function mount($chatbot, $user, $website)
    {
        $this->chatbot = $chatbot;
        $this->user = $user;
        $this->website = $website;

        // Use Laravel session for chat session isolation
        $chatSessionId = session('chat_session_id');
        if ($chatSessionId) {
            $session = ChatSession::find($chatSessionId);
        }
        // Use guest IP for session isolation per chatbot

        $this->guestIp = request()->ip();


        // Load previous messages from the database
        $this->messages = ChatSession::where('chat_bot_id', $this->chatbot->id)->where('guest_ip', $this->guestIp)
            ->latest('created_at') // get the latest session
            ->with(['messages' => function ($query) {
                $query->orderBy('created_at');
            }])
            ->first()?->messages
            ->map(function ($msg) {
                return [
                    'type' => $msg->sent_by === 'user' ? 'sent' : 'received',
                    'content' => $msg->message,
                    'sent_by' => $msg->sent_by,
                    'created_at' => $msg->created_at,
                ];
            })
            ->toArray();

        // If no messages, show welcome
        if (empty($this->messages)) {
            $welcome = '👋 Hello! I am Zeon, your AI assistant 🤖. How can I help you today? 😊 | 👋 හෙලෝ! මම Zeon, ඔබගේ AI උපකාරකයා 🤖. අද ඔබට මට උදව් කරන්න පුළුවන්ද? 😊';
            $this->messages[] = [
                'type' => 'received',
                'content' => $welcome,
            ];
        }
    }

    public function send()
    {

        $session = ChatSession::firstOrCreate(
            [
                'user_id' => $this->user ? $this->user->id : null,
                'chat_bot_id' => $this->chatbot->id,
                'guest_ip' => $this->guestIp,
            ],
            [
                'title' => 'Chat with ' . $this->chatbot->website_name,
            ]
        );
        $this->sessionId = $session->id;
        session()->put('chat_session_id', $this->sessionId);

        $content = trim($this->message);
        if ($content === '' || !$this->sessionId) return;

        // Save user message to DB
        ChatMessage::create([
            'chat_session_id' => $this->sessionId,
            'user_id' => $this->user && isset($this->user->id) ? $this->user->id : null,
            'message' => $content,
            'sent_by' => 'user',
        ]);

        $this->messages[] = [
            'type' => 'sent',
            'content' => $content,
        ];

        $this->message = '';
        $this->placeholder = true;

        $this->dispatch('scroll-chat');

        if ($this->handoverActive) {
            return  $this->messages[] = [
                'type' => 'received',
                'content' => 'Please wait, connecting you to a human agent...',
            ];
        }

        $this->dispatch('chat-response', userMessage: $content);
    }

    #[On('chat-response')]
    public function loadResponse(string $userMessage)
    {
        if (!$this->sessionId) return;

        $userMessage = strip_tags($userMessage);
        $businessInfo = $this->retrieveRelevantInfo();

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
        - Return a clean and well spaces with <p> tags.
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

        $contextMessages = [];

        foreach ($this->messages as $msg) {
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

        // Limit context history to last 10 entries
        $historyLimit = 10;
        $contextMessages = array_slice($contextMessages, -$historyLimit);

        // Add current user question
        $contextMessages[] = new MessageData(
            role: RoleType::USER,
            content: strip_tags($userMessage)
        );

        // Add prompt as system message at the beginning
        array_unshift($contextMessages, new MessageData(
            role: RoleType::SYSTEM,
            content: $prompt
        ));

        $chatData = new ChatData(
            messages: $contextMessages,
            model: 'google/gemini-2.0-flash-lite-001',
            temperature: 0.7,
            top_p: 0.7,
        );

        try {
            $response = LaravelOpenRouter::chatRequest($chatData);
            $reply = Arr::get($response->choices[0], 'message.content', '🙇‍♂️ Sorry, something went wrong.');
        } catch (\Exception $e) {
            $reply = '⚠️ Zeon is temporarily unavailable. Please try again later.';
        }

        // Save bot reply to DB
        ChatMessage::create([
            'chat_session_id' => $this->sessionId,
            'user_id' => null,
            'message' => $reply,
            'sent_by' => 'bot',
        ]);

        $this->messages[] = [
            'type' => 'received',
            'content' => $reply,
        ];

        $this->message = '';
        $this->placeholder = false;

        $this->dispatch('recived-sound');
        $this->dispatch('scroll-chat');

        session(['handoverNeeded' => false]);
    }

    public function handoverToAgent()
    {
        $this->dispatch('handover-to-agent');
        $this->handoverActive = true;
    }

    #[On('handover-to-agent')]
    public function initiateHandover()
    {
        // Add logic to initiate the handover process here
        // This could involve:
        // - Displaying a message to the user
        // - Sending a notification to an agent
        // - Redirecting the user to a different page
        $this->messages[] = [
            'type' => 'received',
            'content' => 'Please wait, connecting you to a human agent...',
        ];
    }

    private function retrieveRelevantInfo()
    {
        try {
            $results = $this->chatbot->businessData->pluck('content')->toArray() ?? [];
            if (empty($results)) {
                return '🙇‍♂️ Sorry, I cannot answer that question based on our current business data.';
            }
            return implode(" ", $results);
        } catch (\Exception $e) {
            return '⚠️ Zeon is temporarily unavailable. Please try again later.';
        }
    }
};

?>

<div x-data="{}" x-clock>

    <style>
        @keyframes blink {

            0%,
            80%,
            100% {
                opacity: 0
            }

            40% {
                opacity: 1
            }
        }

        .dot-anim {
            animation: blink 1.4s infinite;
            display: inline-block;
        }

        .dot-anim.delay-200 {
            animation-delay: 0.2s;
        }

        .dot-anim.delay-400 {
            animation-delay: 0.4s;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fadeInUp 0.3s ease-out;
        }


        .dot-anim {
            animation: blink 1.4s infinite;
            display: inline-block;
        }

        .dot-anim:nth-child(2) {
            animation-delay: 0.2s;
        }

        .dot-anim:nth-child(3) {
            animation-delay: 0.4s;
        }
    </style>

    <script
        src="https://unpkg.com/@dotlottie/player-component@2.7.12/dist/dotlottie-player.mjs"
        type="module"></script>

    <div x-transition style="box-shadow: 0 0 #0000, 0 0 #0000, 0 1px 2px 0 rgb(0 0 0 / 0.05);"
        class="fixed bottom-[calc(6rem+1.5rem)] left-2 right-2 lg:left-auto lg:right-4 bg-white p-0 rounded-lg border border-[#e5e7eb] w-full h-[550px] lg:w-[480px] lg:h-[500px]  flex flex-col">

        <!-- Sticky Header -->
        <div class="flex items-center gap-3 sticky top-0 bg-white z-10 p-4 border-b border-gray-200">
            <div class="flex flex-col space-y-1.5">
                <h2 class="font-semibold text-lg tracking-tight">Chat Assistant ⚡</h2>
                <p class="text-sm text-[#6b7280] leading-3 flex items-center gap-2">
                    Powered by ZENAX
                    <span class="bg-amber-100 text-amber-600 text-[10px] font-semibold px-2 py-0.5 rounded-full">
                        Beta v1.0
                    </span>
                </p>
            </div>

        </div>

        <!-- Scrollable Message Section -->
        <div class="flex-1 overflow-y-auto px-4 py-2 space-y-4" id="chat-container">
            @foreach ($messages as $msg)
            <div class="animate-fade-in transition-all duration-300" x-transition.opacity>
                @if ($msg['type'] === 'received')
                <div wire:ignore class="flex items-start">
                    <dotlottie-player
                        wire:ignore
                        src="https://lottie.host/94673f52-5ca9-4fa5-a6c6-7bceef4c3668/aA2rFgI1Lj.lottie"
                        background="transparent"
                        speed="1"
                        style="width: 50px; height: 50px"
                        loop
                        autoplay>
                    </dotlottie-player>

                    <div class="ml-3 bg-gray-100 p-3 rounded-lg max-w-[75%] w-fit overflow-x-hidden hover:shadow-lg transition-shadow duration-300 animate-fade-in">
                        <div class="text-sm text-gray-800 prose prose-sm ">
                            {!! $msg['content'] !!}
                        </div>
                    </div>
                </div>
                @else
                <div class="flex items-end justify-end gap-4">
                    <div class="bg-cyan-500 p-3 rounded-lg animate-fade-in transition-all duration-300">
                        <p class="text-sm text-white">{{ $msg['content'] }}</p>
                    </div>
                </div>
                @endif
            </div>
            @endforeach

            {{-- Typing / Thinking Indicator --}}
            @if($placeholder)
            <div class="ml-3 bg-gray-100 p-3 rounded-lg w-fit animate-pulse">
                <p class="text-sm text-gray-800 font-medium flex space-x-1">
                    <span>Thinking</span>
                    <span class="dot-anim">.</span>
                    <span class="dot-anim">.</span>
                    <span class="dot-anim">.</span>
                </p>
            </div>
            @endif

            {{-- Error Message --}}

            {{-- Scroll to bottom marker --}}
            <div id="bottom-marker" class="h-0"></div>
        </div>

        {{-- Handover to Agent Button --}}
        <div class="border-t border-gray-200 p-4">
            <button wire:click="handoverToAgent"
                class="inline-flex items-center justify-center rounded-md text-sm font-medium text-[#f9fafb] bg-amber-500 hover:bg-[#111827E6] h-10 px-4 py-2">
                Request Handover
            </button>
        </div>

        <!-- Input Box (Fixed at bottom) -->
        <div class="border-t border-gray-200 p-4">
            <form wire:submit="send()" class="flex items-center w-full space-x-2">
                @csrf
                <input wire:model="message" type="text" name="message" id="message"
                    wire:keydown.enter="send()"
                    class="flex h-10 w-full rounded-md border border-[#e5e7eb] px-3 py-2 text-sm placeholder-[#6b7280] focus:outline-none focus:ring-2 focus:ring-[#9ca3af] text-[#030712] focus-visible:ring-offset-2"
                    placeholder="Type your message" />
                <button type="submit"
                    class="inline-flex items-center justify-center rounded-md text-sm font-medium text-[#f9fafb] bg-cyan-700 hover:bg-[#111827E6] h-10 px-4 py-2">
                    Send
                </button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('scroll-chat', () => {

            let msgInput = document.getElementById('message');
            msgInput.value = '';
            setTimeout(() => {
                document.getElementById('bottom-marker')?.scrollIntoView({
                    behavior: 'smooth'
                });
            }, 100);
        });

        let userInteracting = false;

        function scrollChatLoop() {
            setInterval(() => {
                if (userInteracting) return;

                const bottomMarker = document.getElementById('bottom-marker');
                if (bottomMarker) {
                    bottomMarker.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            }, 1000);
        }

        // Detect user scrollbar interaction
        function watchUserScroll() {
            let timeout;

            const container = document.getElementById('chat-container'); // You can target a specific chat container here

            container.addEventListener('mousedown', () => userInteracting = true);
            container.addEventListener('touchstart', () => userInteracting = true);
            container.addEventListener('wheel', () => {
                userInteracting = true;
                clearTimeout(timeout);
                timeout = setTimeout(() => userInteracting = false, 5000); // reset after user stops for 2s
            });

            container.addEventListener('mouseup', () => {
                clearTimeout(timeout);
                timeout = setTimeout(() => userInteracting = false, 5000);
            });

            container.addEventListener('touchend', () => {
                clearTimeout(timeout);
                timeout = setTimeout(() => userInteracting = false, 5000);
            });
        }

        // Start both
        scrollChatLoop();
        watchUserScroll();

        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form[wire\\:submit]');
            const input = document.querySelector('input[wire\\:model="message"]');

            form.addEventListener('submit', function(e) {

                setTimeout(() => {
                    input.value = '';
                    document.getElementById('bottom-marker')?.scrollIntoView({
                        behavior: 'smooth'
                    });
                }, 10); // Delay to allow Livewire to read the value before clearing
            });
        });

        window.addEventListener('recived-sound', () => {
            const audio = new Audio('/sounds/recived.mp3');
            audio.play().catch(e => console.error("Audio play failed:", e));
        });
    </script>

    <script>
        // Session expires after 1 hour (3600 seconds)
        const SESSION_EXPIRY_SECONDS = 3600;

        function generateSessionId() {
            return 'sess-' + Math.random().toString(36).substr(2, 16) + '-' + Date.now();
        }

        function setNewSession() {
            const newId = generateSessionId();
            localStorage.setItem('chat_session_id', newId);
            localStorage.setItem('chat_session_created_at', Date.now().toString());
            return newId;
        }
        let sessionId = localStorage.getItem('chat_session_id');
        let createdAt = parseInt(localStorage.getItem('chat_session_created_at') || '0', 10);
        let now = Date.now();
        if (!sessionId || !createdAt || ((now - createdAt) / 1000) > SESSION_EXPIRY_SECONDS) {
            sessionId = setNewSession();
        }
        document.addEventListener('DOMContentLoaded', function() {
            let input = document.getElementById('chat-session-id');
            if (input) input.value = sessionId;
        });
    </script>

</div>