<?php

use Illuminate\Support\Facades\Route;

/*
 * Stateless routes. Courier webhooks live here rather than in web.php because
 * they arrive with no session and no CSRF token, and must still be answered
 * while the shop is showing its maintenance page.
 */

Route::post('/webhooks/pathao', \App\Http\Controllers\Webhooks\PathaoWebhookController::class)
    ->name('webhooks.pathao');
