<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Chatbot Subscription</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="//unpkg.com/alpinejs" defer></script>
</head>

<body>
    <div>
        @if(session('success'))
        <div
            x-data="{ show: true }"
            x-init="setTimeout(() => show = false, 5000)"
            x-show="show"
            x-transition:enter="transition ease-out duration-500"
            x-transition:enter-start="translate-x-full opacity-0"
            x-transition:enter-end="translate-x-0 opacity-100"
            x-transition:leave="transition ease-in duration-500"
            x-transition:leave-start="translate-x-0 opacity-100"
            x-transition:leave-end="translate-x-full opacity-0"
            class="fixed top-6 right-6 z-50 bg-green-100 border border-green-400 text-green-800 px-6 py-4 rounded-xl shadow-xl w-full max-w-sm">
            <div class="flex items-center space-x-3">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M5 13l4 4L19 7" />
                </svg>
                <div class="flex-1 text-sm font-medium">
                    {{ session('success') }}
                </div>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div
            x-data="{ show: true }"
            x-init="setTimeout(() => show = false, 5000)"
            x-show="show"
            x-transition:enter="transition ease-out duration-500"
            x-transition:enter-start="translate-x-full opacity-0"
            x-transition:enter-end="translate-x-0 opacity-100"
            x-transition:leave="transition ease-in duration-500"
            x-transition:leave-start="translate-x-0 opacity-100"
            x-transition:leave-end="translate-x-full opacity-0"
            class="fixed top-6 right-6 z-50 bg-red-100 border border-red-400 text-red-800 px-6 py-4 rounded-xl shadow-xl w-full max-w-sm">
            <div class="flex items-center space-x-3">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v2m0 4h.01M4.93 4.93l14.14 14.14M1 12a11 11 0 1122 0 11 11 0 01-22 0z" />
                </svg>
                <div class="flex-1 text-sm font-medium">
                    {{ session('error') }}
                </div>
            </div>
        </div>
        @endif

        @yield('content')
    </div>
</body>

</html>