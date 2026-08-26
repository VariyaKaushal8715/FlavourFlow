<?php

namespace App\Providers;

use App\AI\Adapters\FlavourFlowAdapter;
use App\AI\Contracts\AiAdapterInterface;
use App\AI\Contracts\AiEngineInterface;
use App\AI\Contracts\AiEventTrackerInterface;
use App\AI\Core\AiEngine;
use App\AI\Services\AiEventTracker;
use Illuminate\Support\ServiceProvider;

class AiServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(AiAdapterInterface::class, function () {
            $adapterClass = config('ai.adapters.flavourflow', FlavourFlowAdapter::class);

            return new $adapterClass;
        });

        $this->app->singleton(AiEngineInterface::class, function ($app) {
            $engineClass = config('ai.core.engine', AiEngine::class);

            return new $engineClass($app->make(AiAdapterInterface::class));
        });

        $this->app->singleton(AiEventTrackerInterface::class, AiEventTracker::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
