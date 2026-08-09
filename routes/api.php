<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookController;

Route::post('/tgt/webhook', [WebhookController::class, 'handle']);
