<?php

use App\Models\BusinessData;
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

    public function mount($chatbot, $user, $website)
    {
        $this->chatbot = $chatbot;
        $this->user = $user;
        $this->website = $website;

        $this->messages = [
            [
                'type' => 'received',
                'content' => '👋 Hello! I am Zeon, your AI assistant 🤖. How can I help you today? 😊 | 👋 හෙලෝ! මම Zeon, ඔබගේ AI උපකාරකයා 🤖. අද ඔබට මට උදව් කරන්න පුළුවන්ද? 😊',
            ],
        ];
    }

    public function send()
    {
        $content = trim($this->message);
        if ($content === '') return;

        $this->messages[] = [
            'type' => 'sent',
            'content' => $this->message,
        ];

        $this->message = '';
        $this->placeholder = true;

        $this->dispatch('scroll-chat');
        $this->dispatch('chat-response', userMessage: $content);
    }

    #[On('chat-response')]
    public function loadResponse(string $userMessage)
    {
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
            model: 'openrouter/optimus-alpha',
        );

        try {
            $response = LaravelOpenRouter::chatRequest($chatData);
            $reply = Arr::get($response->choices[0], 'message.content', '🙇‍♂️ Sorry, something went wrong.');
        } catch (\Exception $e) {
            $reply = '⚠️ Zeon is temporarily unavailable. Please try again later.';
        }

        $this->messages[] = [
            'type' => 'received',
            'content' => $reply,
        ];

        $this->message = '';
        $this->placeholder = false;

        $this->dispatch('recived-sound');
        $this->dispatch('scroll-chat');
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

        @keyframes blink {

            0%,
            80%,
            100% {
                opacity: 0;
            }

            40% {
                opacity: 1;
            }
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
                <p class="text-sm text-[#6b7280] leading-3">Powered by ZENAX</p>
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
                    <div class="bg-blue-500 p-3 rounded-lg animate-fade-in transition-all duration-300">
                        <p class="text-sm text-white">{{ $msg['content'] }}</p>
                    </div>
                    <svg version="1.0" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="30px" height="30px" viewBox="0 0 64 64" enable-background="new 0 0 64 64" xml:space="preserve" fill="#000000">
                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                        <g id="SVGRepo_iconCarrier">
                            <g>
                                <g>
                                    <path fill="#394240" d="M63.329,57.781C62.954,57.219,53.892,44,31.999,44C10.112,44,1.046,57.219,0.671,57.781 c-1.223,1.84-0.727,4.32,1.109,5.547c1.836,1.223,4.32,0.727,5.547-1.109C7.397,62.117,14.347,52,31.999,52 c17.416,0,24.4,9.828,24.674,10.219C57.446,63.375,58.712,64,60.009,64c0.758,0,1.531-0.219,2.211-0.672 C64.056,62.102,64.556,59.621,63.329,57.781z"></path>
                                    <path fill="#394240" d="M31.999,40c8.836,0,16-7.16,16-16v-8c0-8.84-7.164-16-16-16s-16,7.16-16,16v8 C15.999,32.84,23.163,40,31.999,40z M23.999,16c0-4.418,3.586-8,8-8c4.422,0,8,3.582,8,8v8c0,4.418-3.578,8-8,8 c-4.414,0-8-3.582-8-8V16z"></path>
                                </g>
                                <path fill="#F9EBB2" d="M23.999,16c0-4.418,3.586-8,8-8c4.422,0,8,3.582,8,8v8c0,4.418-3.578,8-8,8c-4.414,0-8-3.582-8-8V16z"></path>
                            </g>
                        </g>
                    </svg>
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


        <!-- Input Box (Fixed at bottom) -->
        <div class="border-t border-gray-200 p-4">
            <form wire:submit="send()" class="flex items-center w-full space-x-2">
                @csrf
                <input wire:model="message" type="text" name="message" id="message"
                    wire:keydown.enter="send()"
                    class="flex h-10 w-full rounded-md border border-[#e5e7eb] px-3 py-2 text-sm placeholder-[#6b7280] focus:outline-none focus:ring-2 focus:ring-[#9ca3af] text-[#030712] focus-visible:ring-offset-2"
                    placeholder="Type your message" />
                <button type="submit"
                    class="inline-flex items-center justify-center rounded-md text-sm font-medium text-[#f9fafb] bg-black hover:bg-[#111827E6] h-10 px-4 py-2">
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
</div>