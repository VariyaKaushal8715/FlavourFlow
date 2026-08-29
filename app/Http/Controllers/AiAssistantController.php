<?php

namespace App\Http\Controllers;

use App\AI\Contracts\AiBrainInterface;
use App\AI\Services\CustomerAiAssistantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiAssistantController extends Controller
{
    public function __construct(private CustomerAiAssistantService $assistant) {}

    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $response = $this->assistant->processMessage(
            message: $validated['message'],
            userId: $request->user()?->id,
            sessionId: session()->getId()
        );

        return response()->json([
            'success' => true,
            'response' => $response,
            'provider_available' => app(AiBrainInterface::class)->isReady(),
        ]);
    }

    public function history(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'history' => $this->assistant->getHistory(),
            'provider_available' => app(AiBrainInterface::class)->isReady(),
        ]);
    }

    public function clear(): JsonResponse
    {
        $this->assistant->clearHistory();

        return response()->json([
            'success' => true,
            'message' => 'Chat history cleared.',
            'provider_available' => app(AiBrainInterface::class)->isReady(),
        ]);
    }
}
