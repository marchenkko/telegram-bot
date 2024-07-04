<?php

namespace App\Http\Controllers;

use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

class TelegramController extends Controller
{
    protected $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function webhook(Request $request): JsonResponse
    {
        $update = $request->all();
        Log::info('Webhook received', $update);

        if (isset($update['message']['chat']['id'])) {
            $chatId = $update['message']['chat']['id'];
            $username = $update['message']['from']['username'] ?? null;

            $this->telegramService->storeOrUpdateUser($chatId, $username);

            if (isset($update['message']['text']) && $update['message']['text'] === '/start') {
                $message = $username ? "Welcome, $username!" : "Welcome!";
                $this->telegramService->sendMessage($chatId, $message);
            }
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     *
     * @return JsonResponse
     */
    public function registerWebhook(): JsonResponse
    {
        if ($this->telegramService->registerWebhook()) {
            return response()->json(['status' => 'Webhook registered successfully']);
        } else {
            return response()->json(['status' => 'Failed to register webhook'], 500);
        }
    }
}
