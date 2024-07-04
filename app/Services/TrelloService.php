<?php

namespace App\Services;

use App\Models\Webhook;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TrelloService
{
    /**
     * @param string $idBoard
     * @return array|null
     */
    public function getLists(string $idBoard): ?array
    {
        $apiKey = env('TRELLO_API_KEY');
        $apiToken = env('TRELLO_API_TOKEN');

        $url = "https://api.trello.com/1/boards/$idBoard/lists?key=$apiKey&token=$apiToken";

        $response = Http::get($url);

        if ($response->successful()) {
            return $response->json();
        } else {
            Log::error('Failed to retrieve lists from Trello: ' . $response->body());
            return null;
        }
    }

    /**
     * @param string $name
     * @param string $idBoard
     * @return array|null
     */
    public function createList(string $name, string $idBoard): ?array
    {
        $apiKey = env('TRELLO_API_KEY');
        $apiToken = env('TRELLO_API_TOKEN');

        $url = "https://api.trello.com/1/lists?name=" . urlencode($name) . "&idBoard=$idBoard&key=$apiKey&token=$apiToken";

        $response = Http::post($url);

        if ($response->successful()) {
            return $response->json();
        } else {
            Log::error("Failed to create list '$name': " . $response->body());
            return null;
        }
    }

    /**
     * @param string $callbackURL
     * @param string $idModel
     * @return array|null
     * @throws ConnectionException
     */
    public function createWebhook(string $callbackURL, string $idModel): ?array
    {
        $apiKey = env('TRELLO_API_KEY');
        $apiToken = env('TRELLO_API_TOKEN');

        $callbackURL = urlencode($callbackURL);
        $idModel = urlencode($idModel);

        $url = "https://api.trello.com/1/webhooks/?callbackURL=$callbackURL&idModel=$idModel&key=$apiKey&token=$apiToken";

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($url, []);
        if ($response->successful()) {
            $data = $response->json();
            if (isset($data['id'])) {
                Webhook::create([
                    'service' => 'trello',
                    'webhook_id' => $data['id']
                ]);
                return $data;
            }
        }

        Log::error('Failed to create Trello webhook: ' . $response->body());
        return null;
    }

    /**
     * @param string $webhookId
     * @return bool
     */
    public function deleteWebhook(string $webhookId): bool
    {
        $apiKey = env('TRELLO_API_KEY');
        $apiToken = env('TRELLO_API_TOKEN');
        $url = "https://api.trello.com/1/webhooks/$webhookId?key=$apiKey&token=$apiToken";

        $response = Http::delete($url);

        if ($response->successful()) {
            Webhook::where('webhook_id', $webhookId)->delete();
            return true;
        } else {
            Log::error('Failed to delete Trello webhook: ' . $response->body());
            return false;
        }
    }

    /**
     * @return string|null
     */
    public function getWebhookId(): ?string
    {
        $webhook = Webhook::where('service', 'trello')->first();
        return $webhook ? $webhook->webhook_id : null;
    }
}
