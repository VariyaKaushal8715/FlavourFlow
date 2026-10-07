<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CashfreeWebhookController extends Controller
{
    public function handleWebhook(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
        ]);
    }
}
