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
];
