<x-filament-panels::page>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://unpkg.com/@dotlottie/player-component@2.7.12/dist/dotlottie-player.mjs" type="module"></script>
    <div class="flex h-[70vh] border rounded shadow bg-white overflow-hidden">
        <!-- Sidebar: Sessions -->
        <div class="w-1/3 border-r bg-gray-50 overflow-y-auto">
            <div class="p-4 font-bold text-lg border-b">Conversations</div>
            <ul style="padding: 10px;">
                @forelse ($sessions as $session)
                <li>
                    <a
                        href="{{ route(\Illuminate\Support\Facades\Route::currentRouteName(), ['session' => $session->id]) }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-150
                                {{ $selectedSession && $selectedSession->id === $session->id
                                    ? 'bg-blue-100 font-semibold shadow'
                                    : 'hover:bg-blue-50'
                                }}">
                        <span class="flex items-center justify-center w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-cyan-500 text-white text-lg font-bold shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8s-9-3.582-9-8 4.03-8 9-8 9 3.582 9 8z" />
                            </svg>
                        </span>
                        <span class="flex flex-col min-w-0">
                            <span class="truncate text-base leading-tight">
                                {{ $session->title ?? 'Session #' . $session->id }}
                            </span>
                            <span class="text-xs text-gray-500 flex gap-2">
                                <span class="font-mono text-blue-600">#{{ $session->id }}</span>
                                <span>{{ $session->updated_at->format('Y-m-d H:i') }}</span>
                            </span>
                        </span>
                    </a>
                </li>
                @empty
                <li class="px-4 py-3 text-gray-400">No conversations found.</li>
                @endforelse
            </ul>
        </div>
        <!-- Chat Log -->
        <div wire:poll.15s.keep-alive class="flex-1 flex flex-col">
            <div class="flex-1 overflow-y-auto px-4 py-2 space-y-4 h-[65vh] bg-white" id="chat-container">
                @if ($selectedSession)
                @forelse ($messages as $message)
                <div class="animate-fade-in transition-all duration-300" x-transition.opacity>
                    @if ($message->sent_by === 'bot')
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
                            <div class="text-sm text-gray-800 prose prose-sm">
                                {!! $message->message !!}
                            </div>
                            <div class="text-xs text-gray-400 mt-1 text-right">
                                {{ $message->created_at->format('H:i') }}
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="flex items-end justify-end gap-4">
                        <div class="bg-cyan-500 p-3 rounded-lg animate-fade-in transition-all duration-300 max-w-[75%] w-fit">
                            <p class="text-sm text-white mb-0">{{ $message->message }}</p>
                            <div class="text-xs text-gray-200 mt-1 text-right">
                                {{ $message->created_at->format('H:i') }}
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                @empty
                <div class="text-gray-400">No messages yet.</div>
                @endforelse
                @else
                <div class="text-gray-400">Select a conversation to view messages.</div>
                @endif
                {{-- Scroll to bottom marker --}}
                <div id="bottom-marker" class="h-0"></div>
            </div>
        </div>

        <!-- Agent Reply Input -->
        @if ($selectedSession)
        <div class="p-4 border-t bg-gray-50">
            <form wire:submit.prevent="sendReply" class="flex items-center gap-2">
                <input
                    wire:model="agentReply"
                    type="text"
                    placeholder="Type your reply..."
                    class="flex-1 border rounded-md p-2" />
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md">
                    Send
                </button>
            </form>
        </div>
        @endif
    </div>
</x-filament-panels::page>