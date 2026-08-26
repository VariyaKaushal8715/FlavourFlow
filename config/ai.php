<?php

use App\AI\Adapters\FlavourFlowAdapter;
use App\AI\Core\AiEngine;
use App\AI\Core\AiRequest;
use App\AI\Core\AiResponse;

return [
    /*
    |--------------------------------------------------------------------------
    | FlavourFlow Reusable AI Engine Configuration
    |--------------------------------------------------------------------------
    | Step 1: Provider-independent AI core architecture.
    | Business logic is kept separate via domain adapters.
    */

    'enabled' => true,

    'mode' => 'development', // development | production

    'default_adapter' => 'flavourflow',

    'adapters' => [
        'flavourflow' => FlavourFlowAdapter::class,
    ],

    'core' => [
        'engine' => AiEngine::class,
        'request' => AiRequest::class,
        'response' => AiResponse::class,
    ],

    'features' => [
        'recommendations' => true,
        'chat' => true,
        'analytics_insights' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Provider (Step 4)
    |--------------------------------------------------------------------------
    | Provider class for LLM reasoning. Uses NullProvider by default (deterministic).
    | Swap to OpenRouter, Groq, Gemini, OpenAI, or Ollama when ready.
    */

    'provider' => [
        'class' => \App\AI\Providers\NullProvider::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Brain (Step 4)
    |--------------------------------------------------------------------------
    | Supported languages for multilingual reasoning output.
    */

    'brain' => [
        'languages' => ['en', 'hi', 'gu', 'hinglish', 'gujenglish'],
        'default_language' => 'en',
    ],
];
