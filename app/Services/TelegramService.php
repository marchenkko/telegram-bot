<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    /**
     *
     * @param int $chatId
     * @param string $message
     */
    public function sendMessage(int $chatId, string $message)
    {
        $token = env('TELEGRAM_BOT_TOKEN');
        $url = "https://api.telegram.org/bot$token/sendMessage";

        $params = [
            'chat_id' => $chatId,
            'text' => $message,
        ];

        $response = Http::post($url, $params);

        if ($response->successful()) {
            Log::info('Message sent to Telegram: ' . $message);
        } else {
            Log::error('Failed to send message to Telegram: ' . $response->body());
        }
    }

    /**
     *
     * @return bool
     */
    public function registerWebhook(): bool
    {
        $token = env('TELEGRAM_BOT_TOKEN');
        $url = 'https://216d-195-140-225-123.ngrok-free.app/webhook';

        $params = [
            'url' => $url,
        ];

        $response = Http::post("https://api.telegram.org/bot$token/setWebhook", $params);
        if ($response->successful()) {
            return true;
        } else {
            Log::error('Failed to register webhook: ' . $response->body());
            return false;
        }
    }

    /**
     *
     * @param int $chatId
     * @param string|null $username
     */
    public function storeOrUpdateUser(int $chatId, ?string $username)
    {
        $user = User::where('chat_id', $chatId)->first();
        if ($user) {
            $user->username = $username;
            $user->save();
        } else {
            User::create([
                'chat_id' => $chatId,
                'username' => $username
            ]);
        }
    }
}
