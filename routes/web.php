<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Models\ChatBot;
use Laravel\Cashier\Http\Controllers\WebhookController;

Route::get('/', function () {
    return view('welcome', [
        'chatbot' => $chatbot ?? null,
        'user' => $userModel ?? null,
        'website' => $website ?? null,
    ]);
});

Route::get('/embed/chatbot', function (Request $request) {

    $website = $request->query('website'); // or $request->get('website')
    $user = $request->query('user'); //uuid
    $chatbot_id = $request->query('chatbot_id'); //chatbot_id

    if (!$website || !$user || !$chatbot_id) {
        abort(404);
    }

    $website = urldecode($website) ?? '';
    $user = urldecode($user) ?? '';
    $chatbot_id = urldecode($chatbot_id) ?? '';

    // Find user by UUID
    $userModel = User::where('uuid', $user)->first();

    if (!$userModel) {
        abort(404);
    }

    $subscription = $userModel->subscriptions()->where('stripe_status', 'active')->first();

    // Check subscription status
    if (!$subscription) {
        abort(404);
    }

    // Find chatbot by ID belonging to this user and matching website
    $chatbot = ChatBot::where('id', $chatbot_id)
        ->where('user_id', $userModel->id)
        ->where('website_url', $website)
        ->where('widget_code', '!=', null) // Ensure widget_code is not null
        ->where('status', true) // Ensure chatbot is active
        ->first() ?? null;

    if (!$chatbot) {
        abort(404);
    }


    // Pass chatbot data to the view (replace 'welcome' with your chatbot view)
    return view('welcome', [
        'chatbot' => $chatbot ?? null,
        'user' => $userModel ?? null,
        'website' => $website ?? null,
    ]);
});
Route::middleware(['auth'])->group(function () {
    Route::get('/subscription', [\App\Http\Controllers\SubscriptionController::class, 'show'])->name('subscription.show');
    Route::post('/subscribe', [\App\Http\Controllers\SubscriptionController::class, 'subscribe'])->name('subscription.subscribe');
    Route::post('/subscription/cancel', [\App\Http\Controllers\SubscriptionController::class, 'cancel'])->name('subscription.cancel');
    Route::get('/subscription/success', [\App\Http\Controllers\SubscriptionController::class, 'success'])->name('subscription.success');
    Route::post('/subscription/resume', [\App\Http\Controllers\SubscriptionController::class, 'resume'])->name('subscription.resume');
});

Route::post('/stripe/webhook', [WebhookController::class, 'handleWebhook']);
