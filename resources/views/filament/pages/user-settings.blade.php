<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
<x-filament::page>
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-6xl mx-auto flex flex-col lg:flex-row gap-8">

            {{-- Profile Settings --}}
            <div class="flex-1 bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-md transition">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-6">My Profile Settings</h2>

                @if (session()->has('success'))
                <div class="bg-emerald-100 dark:bg-emerald-800 border border-emerald-400 dark:border-emerald-600 text-emerald-700 dark:text-emerald-100 text-center px-4 py-3 rounded mb-6 font-medium">
                    {{ session('success') }}
                </div>
                @endif

                <form wire:submit.prevent="save" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name</label>
                        <input wire:model.defer="name" type="text" value="{{ auth()->user()->name }}" readonly
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                            required />
                        @error('name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                        <input wire:model.defer="email" type="email" value="{{ auth()->user()->email }}" readonly
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                            required />
                        @error('email') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Your Unique UUID</label>
                        <span class="block bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-black text-sm px-4 py-2 rounded-lg break-all">
                            {{ $uuid }}
                        </span>
                    </div>

                    <div class="pt-4 border-t dark:border-gray-600">
                        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">Change Password</h3>

                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">New Password</label>
                                <input wire:model.defer="password" type="password"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500" />
                                @error('password') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Confirm New Password</label>
                                <input wire:model.defer="password_confirmation" type="password"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500" />
                            </div>
                        </div>
                    </div>

                    <div class="text-right pt-6">
                        <button type="submit"
                            class="bg-primary-600 hover:bg-primary-700 text-white font-semibold px-6 py-2 rounded-lg transition">
                            Save Settings
                        </button>
                    </div>
                </form>
            </div>

            {{-- Subscription Card --}}
            <div class="w-full lg:max-w-sm bg-white dark:bg-gray-800 p-8 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700 relative overflow-hidden">
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
                        <span class="bg-primary-100 dark:bg-primary-700 text-primary-800 dark:text-black text-xs px-3 py-1 rounded-full font-semibold">
                            {{ $subscription->product_name ?? 'N/A' }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium">Status:</span>
                        <span class="text-sm font-semibold text-green-600 dark:text-green-400">{{ $subscription->stripe_status }}</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium">Recurring:</span>
                        <span class="text-sm text-gray-600 dark:text-gray-400">Monthly</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium">Ends At:</span>
                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ $subscription->ends_at ?? 'Active' }}</span>
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
</x-filament::page>