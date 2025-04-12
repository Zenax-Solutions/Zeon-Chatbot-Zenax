<div class="p-6 bg-white rounded-xl shadow text-center" style="display: flex;align-items: center;">
    @php
    $subscription = auth()->user()->subscriptions()->where('stripe_status', 'active')->first();
    $planName = $subscription->name ?? 'No Plan';
    $planImage = asset('images/plans/' . $planName . '.png');
    @endphp

    <iframe src="https://lottie.host/embed/94673f52-5ca9-4fa5-a6c6-7bceef4c3668/aA2rFgI1Lj.lottie"></iframe>
    <h3 class="text-lg font-bold">{{ ucfirst($planName) }} Plan ⚡🚀</h3>
</div>