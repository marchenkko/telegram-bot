<?php

use App\Http\Controllers\TelegramController;
use App\Http\Controllers\TrelloWebhookController;
use Illuminate\Support\Facades\Route;

Route::any('/trello-webhook', [TrelloWebhookController::class, 'handleWebhook']);
Route::get('/trello-webhook-delete', [TrelloWebhookController::class, 'deleteWebhook']);
Route::get('/trello-webhook-create', [TrelloWebhookController::class, 'createWebhook']);

Route::get('/trello-list-get', [TrelloWebhookController::class, 'getLists']);
Route::get('/trello-list-create', [TrelloWebhookController::class, 'createLists']);

Route::post('/webhook', [TelegramController::class, 'webhook']);
Route::get('/telegram-webhook-create', [TelegramController::class, 'registerWebhook']);


