<?php

namespace App\Http\Controllers;

use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Stripe\StripeClient;

class SubscriptionController extends Controller
{
    public function show()
    {
        $stripe = new StripeClient(config('cashier.secret'));

        $plans = ['basic', 'standard', 'premium'];
        $products = [];
        $prices = [];

        foreach ($plans as $plan) {
            $productId = config('cashier.products.' . $plan);
            $products[$plan] = $stripe->products->retrieve($productId, []);
            $planPrices = $stripe->prices->all(['product' => $productId, 'limit' => 1]);
            $prices[$plan] = $planPrices->data[0] ?? null;
        }

        $user = auth()->user();
        $activeSubscription = $user->subscriptions()->where('stripe_status', 'active')->first();
        $currentPlan = $activeSubscription->name ?? null;

        $tiers = ['basic' => 1, 'standard' => 2, 'premium' => 3];

        return view('subscription', [
            'products' => $products,
            'prices' => $prices,
            'currentPlan' => $currentPlan,
            'tiers' => $tiers,
        ]);
    }

    public function subscribe(Request $request)
    {
        $user = $request->user();

        $stripe = new StripeClient(config('cashier.secret'));

        $selectedPlan = $request->input('plan', 'basic'); // default to basic if not set
        $couponCode = $request->input('coupon'); // get coupon code from request
        $productId = config('cashier.products.' . $selectedPlan, config('cashier.products.basic'));

        $prices = $stripe->prices->all(['product' => $productId, 'limit' => 1]);
        $priceId = $prices->data[0]->id ?? null;

        if (!$priceId) {
            return back()->with('error', 'Subscription price not found.');
        }

        $tiers = ['basic' => 1, 'standard' => 2, 'premium' => 3];

        $activeSubscription = $user->subscriptions()->where('stripe_status', 'active')->first();
        $currentPlan = $activeSubscription->name ?? null;
        $currentTier = $tiers[$currentPlan] ?? 0;
        $selectedTier = $tiers[$selectedPlan] ?? 0;

        if ($activeSubscription) {
            if ($selectedTier > $currentTier) {

                // Create new checkout session for upgrade
                $checkoutSession = $stripe->checkout->sessions->create([
                    'customer_email' => $user->email,
                    'payment_method_types' => ['card'],
                    'line_items' => [[
                        'price' => $priceId,
                        'quantity' => 1,
                    ]],
                    'mode' => 'subscription',
                    'discounts' => $couponCode ? [['coupon' => $couponCode]] : [],
                    'success_url' => route('subscription.success') . '?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url' => route('subscription.show'),
                ]);
                return redirect($checkoutSession->url);
            } else {
                return back()->with('error', 'You are already on this plan or a higher plan.');
            }
        }

        // Not subscribed, create new checkout session
        $checkoutSession = $stripe->checkout->sessions->create([
            'customer_email' => $user->email,
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price' => $priceId,
                'quantity' => 1,
            ]],
            'mode' => 'subscription',
            'discounts' => $couponCode ? [['coupon' => $couponCode]] : [],
            'success_url' => route('subscription.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('subscription.show'),
        ]);

        return redirect($checkoutSession->url);
    }

    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');

        $stripe = new StripeClient(config('cashier.secret'));
        $session = $stripe->checkout->sessions->retrieve($sessionId, ['expand' => ['subscription']]);

        $subscription = $session->subscription;

        // Fetch the product name from Stripe
        $productId = $subscription->items->data[0]->price->product ?? null;
        $productName = null;
        if ($productId) {
            try {
                $product = $stripe->products->retrieve($productId, []);
                $productName = $product->name ?? null;
            } catch (\Exception $e) {
                $productName = null; // Fallback if API call fails
            }
        }

        $user = auth()->user();

        // Attach Stripe customer ID to user
        $user->stripe_id = $session->customer;
        $user->save();

        // Determine plan key (basic, standard, premium) based on product ID
        $planKey = 'basic';
        $products = config('cashier.products', []);
        foreach ($products as $key => $id) {
            if ($id === $productId) {
                $planKey = $key;
                break;
            }
        }

        // Cancel all existing active subscriptions
        $activeSubscriptions = $user->subscriptions()->where('stripe_status', 'active')->get();
        foreach ($activeSubscriptions as $activeSub) {
            try {
                $activeSub->cancelNow();
            } catch (\Exception $e) {
                // Ignore errors
            }
        }

        // Save new subscription record
        $user->subscriptions()->updateOrCreate(
            ['stripe_id' => $subscription->id],
            [
                'name' => $planKey,
                'type' => $planKey,
                'product_name' => $productName,
                'stripe_status' => $subscription->status,
                'stripe_price' => $subscription->items->data[0]->price->id ?? null,
                'quantity' => 1,
                'trial_ends_at' => null,
                'ends_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        Notification::make()
            ->title('Subscription successful!')
            ->body('You have successfully subscribed to the ' . $productName . ' plan.')
            ->success()
            ->send();

        return redirect('/admin')->with('success', 'Subscription successful!');
    }

    public function resume(Request $request)
    {
        $subscription = $request->user()->subscriptions()->where('stripe_status', 'active')->first();

        if ($subscription && $subscription->onGracePeriod()) {
            $subscription->resume();

            Notification::make()
                ->title('Subscription reactivated!')
                ->body('Your subscription has been reactivated.')
                ->success()
                ->send();

            return back()->with('success', 'Subscription reactivated successfully.');
        }

        Notification::make()
            ->title('No subscription to reactivate!')
            ->body('You do not have a subscription to reactivate.')
            ->danger()
            ->send();

        return back()->with('error', 'Unable to reactivate subscription.');
    }

    public function cancel(Request $request)
    {
        $subscription = $request->user()->subscriptions()->where('stripe_status', 'active')->first();

        if ($subscription) {
            $subscription->cancel();

            Notification::make()
                ->title('Subscription cancelled!')
                ->body('Your subscription has been cancelled.')
                ->success()
                ->send();

            return back()->with('success', 'Subscription cancelled.');
        }


        Notification::make()
            ->title('No active subscription!')
            ->body('You do not have an active subscription to cancel.')
            ->danger()
            ->send();

        return back()->with('error', 'No active subscription found.');
    }
}
