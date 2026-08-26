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

    'mode' => env('AI_MODE', 'development'), // development | production

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
    | AI Provider System (Step 6)
    |--------------------------------------------------------------------------
    | Configurable LLM provider drivers: openrouter, groq, gemini, openai, ollama, null.
    | Secrets are loaded exclusively from .env via env().
    */

    'default_provider' => env('AI_PROVIDER', 'openrouter'),

    'providers' => [
        'openrouter' => [
            'api_key' => env('OPENROUTER_API_KEY', ''),
            'model' => env('OPENROUTER_MODEL', 'google/gemini-2.0-flash-001'),
            'base_url' => env('OPENROUTER_BASE_URL', 'https://openrouter.ai/api/v1'),
            'timeout' => (int) env('OPENROUTER_TIMEOUT', 15),
        ],
        'groq' => [
            'api_key' => env('GROQ_API_KEY', ''),
            'model' => env('GROQ_MODEL', 'llama-3.3-70b-versatile'),
            'base_url' => env('GROQ_BASE_URL', 'https://api.groq.com/openai/v1'),
        ],
        'gemini' => [
            'api_key' => env('GEMINI_API_KEY', ''),
            'model' => env('GEMINI_MODEL', 'gemini-2.0-flash'),
        ],
        'openai' => [
            'api_key' => env('OPENAI_API_KEY', ''),
            'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
        ],
        'ollama' => [
            'base_url' => env('OLLAMA_BASE_URL', 'http://localhost:11434'),
            'model' => env('OLLAMA_MODEL', 'llama3'),
        ],
        'null' => [
            'model' => 'deterministic-v1',
        ],
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
