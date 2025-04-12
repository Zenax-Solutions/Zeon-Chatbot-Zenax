<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Page Not Found - 404</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Tailwind CSS CDN for error page fallback (in case build is unavailable) -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class=" min-h-screen flex items-center justify-center">
    <!-- Modal Background -->
    <div id="modal-backdrop" class="fixed inset-0  flex items-center justify-center z-50">
        <!-- Modal Content -->
        <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full text-center animate-fade-in">
            <div class="flex flex-col items-center">
                <svg class="w-20 h-20 text-cyan-400 mb-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 48 48">
                    <circle cx="24" cy="24" r="22" stroke="currentColor" stroke-width="3" fill="#f3e8ff" />
                    <path d="M16 20c0-4.418 3.582-8 8-8s8 3.582 8 8" stroke="#a78bfa" stroke-width="2" stroke-linecap="round" />
                    <rect x="21" y="28" width="6" height="6" rx="3" fill="#a78bfa" />
                </svg>
                <h1 class="text-3xl font-bold text-purple-700 mb-2">Oops! Chatbot Inactive</h1>
                <p class="text-gray-600 mb-6">
                    The chatbot you're trying to access is currently inactive or the credentials provided are invalid.<br>
                    Please check your configuration or contact the site administrator for assistance.
                </p>
            </div>
        </div>
    </div>
    <style>
        @keyframes fade-in {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fade-in 0.7s cubic-bezier(0.4, 0, 0.2, 1);
        }
    </style>
    <script>
        // Optionally, allow closing the modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.getElementById('modal-backdrop').style.display = 'none';
            }
        });
    </script>
</body>

</html>