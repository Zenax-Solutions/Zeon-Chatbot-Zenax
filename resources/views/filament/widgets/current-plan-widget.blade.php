<div>

    @php
    $subscription = auth()->user()->subscriptions()->where('stripe_status', 'active')->first();
    $planName = strtolower($subscription->name ?? 'default'); // basic, standard, premium
    $planClass = match ($planName) {
    'basic' => 'basic-theme',
    'standard' => 'standard-theme',
    'premium' => 'premium-theme',
    default => 'default-theme',
    };
    @endphp

    <style>
        .plan-card {
            position: relative;
            display: flex;
            align-items: center;
            gap: 24px;
            padding: 24px 32px;
            border-radius: 18px;
            color: #fff;
            overflow: hidden;
            isolation: isolate;
            box-shadow:
                0 2px 6px rgba(0, 0, 0, 0.1),
                0 10px 20px rgba(0, 0, 0, 0.3);
        }

        /* Theme: Basic */
        .basic-theme {
            background: linear-gradient(135deg, #1f2937, #4b5563);
        }

        .basic-theme::before {
            content: "";
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at top left, #3b82f6 0%, transparent 60%);
            opacity: 0.2;
            z-index: 0;
        }

        /* Theme: Standard */
        .standard-theme {
            background: linear-gradient(135deg, #1e293b, #64748b);
        }

        .standard-theme::before {
            content: "";
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at top left, rgb(9, 136, 255) 0%, transparent 60%);
            opacity: 0.2;
            z-index: 0;
        }

        /* Theme: Premium */
        .premium-theme {
            background: linear-gradient(135deg, #312e81, rgb(21, 102, 194));
        }

        .premium-theme::before {
            content: "";
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at top right, rgb(0, 104, 201) 0%, transparent 60%);
            opacity: 0.2;
            z-index: 0;
        }

        /* Theme: Default */
        .default-theme {
            background: linear-gradient(135deg, #374151, #6b7280);
        }

        .plan-lottie {
            width: 96px;
            height: 96px;
            border: none;
            border-radius: 12px;
            background: transparent;
            z-index: 1;
        }

        .plan-info {
            z-index: 1;
            text-align: left;
        }

        .plan-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
            letter-spacing: 0.5px;
        }

        .plan-subtext {
            font-size: 0.95rem;
            margin-top: 4px;
            color: #cbd5e1;
        }
    </style>

    <div class="plan-card {{ $planClass }}">
        <iframe
            class="plan-lottie"
            src="https://lottie.host/embed/94673f52-5ca9-4fa5-a6c6-7bceef4c3668/aA2rFgI1Lj.lottie"
            allow="autoplay">
        </iframe>

        <div class="plan-info">
            <h3 class="plan-title">{{ ucfirst($planName) }} Plan ⚡🚀</h3>
            <p class="plan-subtext">You're subscribed to the {{ ucfirst($planName) }} plan.</p>


            <div class="flex items-center ">
                <span class="text-sm font-medium">Ends At:&nbsp;</span>
                <span class="text-sm text-white dark:text-gray-400">{{ $subscription->ends_at ?? ' Active' }}</span>
            </div>
        </div>

    </div>


</div>