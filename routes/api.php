<?php

use App\Http\Controllers\TelegramWebhookController;
use Illuminate\Support\Facades\Route;

// Telegram Bot webhook — primary command channel
Route::post('/webhooks/telegram', [TelegramWebhookController::class, 'handle'])
    ->middleware('throttle:120,1');
