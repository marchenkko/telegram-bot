<?php

namespace App\Http\Controllers;

use App\Services\TelegramService;
use App\Services\TrelloService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

class TrelloWebhookController extends Controller
{
    protected $telegramService;
    protected $trelloService;

    public function __construct(TelegramService $telegramService, TrelloService $trelloService)
    {
        $this->telegramService = $telegramService;
        $this->trelloService = $trelloService;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        Log::info('Webhook received', $request->all());

        $data = $request->all();

        if (isset($data['action']['type']) && $data['action']['type'] === 'updateCard' &&
            isset($data['action']['data']['listBefore']) && isset($data['action']['data']['listAfter'])) {

            $cardName = $data['action']['data']['card']['name'];
            $listBefore = $data['action']['data']['listBefore']['name'];
            $listAfter = $data['action']['data']['listAfter']['name'];

            Log::info("Card '$cardName' moved from '$listBefore' to '$listAfter'");

            if (($listBefore === 'In Progress' && $listAfter === 'Done') ||
                ($listBefore === 'Done' && $listAfter === 'In Progress')) {

                $message = "Card '$cardName' moved from '$listBefore' to '$listAfter'";
                $this->telegramService->sendMessage(env('TELEGRAM_CHAT_ID'), $message);
            }
        } else {
            Log::info('Not an updateCard action or no listBefore/listAfter present');
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * @return JsonResponse
     */
    public function createWebhook(): JsonResponse
    {
        $callbackURL = 'https://216d-195-140-225-123.ngrok-free.app/trello-webhook';
        $idModel = '6685206e43b8a9a9a69ebe48';

        $data = $this->trelloService->createWebhook($callbackURL, $idModel);

        if ($data && isset($data['id'])) {
            return response()->json(['status' => 'Webhook created successfully', 'id' => $data['id']]);
        } else {
            return response()->json(['status' => 'Failed to create webhook'], 500);
        }
    }

    /**
     * @return JsonResponse
     */
    public function deleteWebhook(): JsonResponse
    {
        $webhookId = $this->trelloService->getWebhookId();

        if (!$webhookId) {
            return response()->json(['status' => 'No webhook ID found to delete'], 404);
        }

        if ($this->trelloService->deleteWebhook($webhookId)) {
            return response()->json(['status' => 'Webhook deleted successfully']);
        } else {
            return response()->json(['status' => 'Failed to delete webhook'], 500);
        }
    }

    /**
     * @return JsonResponse
     */
    public function getLists(): JsonResponse
    {
        $idBoard = '6685206e43b8a9a9a69ebe48';
        $lists = $this->trelloService->getLists($idBoard);

        if ($lists) {
            return response()->json($lists);
        } else {
            return response()->json(['status' => 'Failed to retrieve lists'], 500);
        }
    }

    /**
     * @return JsonResponse
     */
    public function createLists(): JsonResponse
    {
        $idBoard = '6685206e43b8a9a9a69ebe48';
        $lists = $this->trelloService->getLists($idBoard);

        if (!$lists) {
            return response()->json(['status' => 'Failed to retrieve lists'], 500);
        }

        $inProgressExists = false;
        $doneExists = false;

        foreach ($lists as $list) {
            if ($list['name'] === 'In Progress') {
                $inProgressExists = true;
            }
            if ($list['name'] === 'Done') {
                $doneExists = true;
            }
        }

        $createdLists = [];

        if (!$inProgressExists) {
            $inProgressList = $this->trelloService->createList('In Progress', $idBoard);
            if ($inProgressList) {
                $createdLists[] = $inProgressList;
            } else {
                return response()->json(['status' => "Failed to create list 'In Progress'"], 500);
            }
        }

        if (!$doneExists) {
            $doneList = $this->trelloService->createList('Done', $idBoard);
            if ($doneList) {
                $createdLists[] = $doneList;
            } else {
                return response()->json(['status' => "Failed to create list 'Done'"], 500);
            }
        }

        if (empty($createdLists)) {
            return response()->json(['status' => 'Lists already exist']);
        }

        return response()->json(['status' => 'Lists created successfully', 'lists' => $createdLists]);
    }
}
