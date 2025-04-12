@extends('layouts.app')

@section('content')
<div class="min-h-screen w-full bg-gradient-to-br from-blue-400 to-sky-700  animate-gradient-x flex items-center justify-center py-10 px-4">
    <div class="w-full max-w-6xl bg-white shadow-2xl rounded-2xl p-8 md:p-12">
        <div class="text-center max-w-2xl mx-auto">
            <h2 class="text-2xl md:text-4xl font-bold text-cyan-700">
                Choose Your Subscription Plan
            </h2>
            <p class="text-gray-600 mt-2 font-bold">Select a plan that fits your journey</p>
        </div>

        <form id="payment-form" action="{{ route('subscription.subscribe') }}" method="POST" class="mt-10">
            @csrf
            @php $currentPlan = $currentPlan ?? null; @endphp

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <input type="radio" name="plan" id="plan_basic" value="basic" class="hidden peer/b" @if($currentPlan==='basic' ) disabled @endif>
                <label for="plan_basic"
                    class="relative group p-6 bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-lg transition-all peer-checked/b:ring-2 peer-checked/b:ring-amber-500 cursor-pointer text-center opacity-100 @if($currentPlan === 'basic') opacity-50 cursor-not-allowed @endif">
                    <h3 class="text-lg font-bold text-gray-800 group-[.peer-checked]/b:text-amber-600">Basic</h3>
                    <p class="text-gray-500 mt-1 group-[.peer-checked]/b:text-amber-400 font-bold">$10 / month</p>
                    @if($currentPlan === 'basic')
                    <span class="mt-2 inline-block bg-green-100 text-green-800 text-xs px-2 py-0.5 rounded-full font-bold">Current Plan</span>
                    @endif
                </label>

                <input type="radio" name="plan" id="plan_standard" value="standard" class="hidden peer/s" @if($currentPlan==='standard' ) disabled @endif>
                <label for="plan_standard"
                    class="relative group p-6 bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-lg transition-all peer-checked/s:ring-2 peer-checked/s:ring-amber-500 cursor-pointer text-center opacity-100 @if($currentPlan === 'standard') opacity-50 cursor-not-allowed @endif">
                    <h3 class="text-lg font-bold text-gray-800 group-[.peer-checked]/s:text-amber-600">Standard</h3>
                    <p class="text-gray-500 mt-1 group-[.peer-checked]/s:text-amber-400 font-bold">$30 / month</p>
                    @if($currentPlan === 'standard')
                    <span class="mt-2 inline-block bg-green-100 text-green-800 text-xs px-2 py-0.5 rounded-full font-bold">Current Plan</span>
                    @endif
                </label>

                <input type="radio" name="plan" id="plan_premium" value="premium" class="hidden peer/p" @if($currentPlan==='premium' ) disabled @endif>
                <label for="plan_premium"
                    class="relative group p-6 bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-lg transition-all peer-checked/p:ring-2 peer-checked/p:ring-amber-500 cursor-pointer text-center opacity-100 @if($currentPlan === 'premium') opacity-50 cursor-not-allowed @endif">
                    <h3 class="text-lg font-bold text-gray-800 group-[.peer-checked]/p:text-amber-600">Premium</h3>
                    <p class="text-gray-500 mt-1 group-[.peer-checked]/p:text-amber-400 font-bold">$60 / month</p>
                    @if($currentPlan === 'premium')
                    <span class="mt-2 inline-block bg-green-100 text-green-800 text-xs px-2 py-0.5 rounded-full font-bold">Current Plan</span>
                    @endif
                </label>
            </div>

            <div class="mt-10">
                <label for="coupon" class="block text-sm font-medium text-gray-700 mb-2">
                    Coupon Code (optional)
                </label>
                <input type="text" name="coupon" id="coupon"
                    class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-800 shadow-sm focus:ring-amber-300 focus:border-amber-500 placeholder-gray-400 transition"
                    placeholder="Enter coupon code">
            </div>

            <div class="mt-6" id="card-element">
                <!-- Stripe Element Here -->
            </div>
            <input type="hidden" name="paymentMethod" id="paymentMethod">

            <div class="mt-8">
                <button id="submit" type="submit"
                    class="w-full bg-gradient-to-br  from-blue-400 to-sky-700 hover:bg-sky-800 text-white font-semibold py-3 px-6 rounded-xl transition">
                    Subscribe Now
                </button>
            </div>
        </form>
    </div>
</div>
@endsection