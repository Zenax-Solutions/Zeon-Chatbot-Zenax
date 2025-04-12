<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureSubscribed
{
    public function handle(Request $request, Closure $next)
    {
        $plans = ['basic', 'standard', 'premium'];

        // If user is not logged in, skip subscription check (allow login page)
        if (!Auth::check()) {
            return $next($request);
        }

        $hasActive = false;
        foreach ($plans as $plan) {
            if (Auth::user()->subscribed($plan) && !Auth::user()->subscription($plan)->ended()) {
                $hasActive = true;
                break;
            }
        }

        if (!$hasActive) {
            return redirect()->route('subscription.show')->with('error', 'You must have an active subscription to access the admin panel.');
        }

        return $next($request);
    }
}
