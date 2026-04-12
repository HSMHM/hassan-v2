<?php

use App\Http\Controllers\TelegramWebhookController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

// Telegram Bot webhook — PRIMARY command channel
Route::post('/webhooks/telegram', [TelegramWebhookController::class, 'handle'])
    ->middleware('throttle:120,1');

// WhatsApp webhook — legacy, kept for backward compatibility
Route::post('/webhooks/whatsapp', [WebhookController::class, 'whatsapp'])
    ->middleware('throttle:60,1');
