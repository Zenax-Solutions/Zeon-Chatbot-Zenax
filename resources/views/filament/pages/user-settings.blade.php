<x-filament-panels::page>

    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <div>
        <div class="w-full mx-auto flex flex-col lg:flex-row gap-8">

            {{-- Profile Settings --}}
            <div class="flex-1 bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-md transition">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-6">My Profile Settings</h2>

                @if (session()->has('success'))
                <div class="bg-emerald-100 dark:bg-emerald-800 border border-emerald-400 dark:border-emerald-600 text-emerald-700 dark:text-emerald-100 text-center px-4 py-3 rounded mb-6 font-medium">
                    {{ session('success') }}
                </div>
                @endif

                <x-filament-panels::form wire:submit="save">
                    {{ $this->form }}

                    <x-filament-panels::form.actions
                        :actions="$this->getFormActions()" />

                </x-filament-panels::form>
            </div>

            {{-- Subscription Card --}}
            <div class="w-full h-[350px] lg:max-w-sm dark:bg-gray-800 p-8 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700 relative overflow-hidden">
                <div class="absolute -top-10 -right-10 w-32 h-32 bg-gradient-to-tr from-primary-100 to-primary-300 dark:from-primary-800 dark:to-primary-500 rounded-full opacity-20 pointer-events-none"></div>

                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                    <svg class="w-6 h-6 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" stroke-width="1.5"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 8c1.657 0 3-1.567 3-3.5S13.657 1 12 1 9 2.567 9 4.5 10.343 8 12 8zm0 0v3m-6 6v-3a6 6 0 0112 0v3" />
                    </svg>
                    My Subscription
                </h2>
                @php
                $subscription = auth()->user()->subscriptions()->where('stripe_status', 'active')->first();
                @endphp

                @if ($subscription && $subscription->valid())
                <div class="space-y-4 pt-4 text-gray-700 dark:text-gray-300">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium">Package:</span>
                        <span style="background-color:orange" class=" dark:bg-primary-700 text-white dark:text-black text-xs px-3 py-1 rounded-full font-semibold">
                            {{ $subscription->product_name ?? 'N/A' }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium">Status:</span>
                        <span class="text-sm font-semibold text-green-600 dark:text-green-400">{{ $subscription->stripe_status }}</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium">Recurring:</span>
                        <span class="text-sm text-white text-active dark:text-gray-400">Monthly</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium">Ends At:</span>
                        <span class="text-sm text-white dark:text-gray-400">{{ $subscription->ends_at ?? 'Active' }}</span>
                    </div>
                </div>

                <form action="{{ route('subscription.cancel') }}" method="POST" class="mt-6">
                    @csrf
                    <button type="submit" style="background-color: red;"
                        class="w-full hover:bg-red-700 text-white font-bold px-6 py-2.5 rounded-lg transition shadow-sm hover:shadow-md">
                        Cancel Subscription
                    </button>
                </form>

                @if ($subscription->ended() || $subscription->onGracePeriod())
                <form action="{{ route('subscription.resume') }}" method="POST" class="mt-3">
                    @csrf
                    <button type="submit" style="background-color: green;"
                        class="w-full hover:bg-emerald-700 text-white font-bold px-6 py-2.5 rounded-lg transition shadow-sm hover:shadow-md">
                        Reactivate Subscription
                    </button>
                </form>
                @endif
                @else
                <div class="text-center text-gray-700 dark:text-gray-300 mb-6">
                    <p>You do not have an active subscription.</p>
                </div>
                <a href="{{ route('subscription.show') }}" style="background-color: green;"
                    class="block w-full text-center  hover:bg-primary-700 text-white font-bold px-6 py-2.5 rounded-lg transition shadow-sm hover:shadow-md">
                    Subscribe Now
                </a>
                @endif
            </div>
        </div>
    </div>


    <x-filament-actions::modals />

</x-filament-panels::page>