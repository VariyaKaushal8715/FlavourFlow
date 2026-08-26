<?php

namespace App\Providers;

use App\AI\Adapters\FlavourFlowAdapter;
use App\AI\Contracts\AiAdapterInterface;
use App\AI\Contracts\AiAnalyzerInterface;
use App\AI\Contracts\AiBrainInterface;
use App\AI\Contracts\AiContextBuilderInterface;
use App\AI\Contracts\AiEngineInterface;
use App\AI\Contracts\AiEventTrackerInterface;
use App\AI\Contracts\AiProviderInterface;
use App\AI\Contracts\AiRecommendationEngineInterface;
use App\AI\Core\AiEngine;
use App\AI\Providers\NullProvider;
use App\AI\Services\AiAnalyzer;
use App\AI\Services\AiBrain;
use App\AI\Services\AiContextBuilder;
use App\AI\Services\AiEventTracker;
use App\AI\Services\AiRecommendationEngine;
use Illuminate\Support\ServiceProvider;

class AiServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Step 1: Core Engine & Adapter
        $this->app->singleton(AiAdapterInterface::class, function () {
            $adapterClass = config('ai.adapters.flavourflow', FlavourFlowAdapter::class);

            return new $adapterClass;
        });

        $this->app->singleton(AiEngineInterface::class, function ($app) {
            $engineClass = config('ai.core.engine', AiEngine::class);

            return new $engineClass($app->make(AiAdapterInterface::class));
        });

        // Step 2: Event Tracking
        $this->app->singleton(AiEventTrackerInterface::class, AiEventTracker::class);

        // Step 3: Context & Analysis
        $this->app->singleton(AiContextBuilderInterface::class, AiContextBuilder::class);
        $this->app->singleton(AiAnalyzerInterface::class, AiAnalyzer::class);

        // Step 4: AI Brain & Provider
        $this->app->singleton(AiProviderInterface::class, function () {
            $providerClass = config('ai.provider.class', NullProvider::class);

            return new $providerClass;
        });

        $this->app->singleton(AiBrainInterface::class, function ($app) {
            return new AiBrain(
                $app->make(AiProviderInterface::class),
                $app->make(AiContextBuilderInterface::class),
                $app->make(AiAnalyzerInterface::class),
            );
        });

        // Step 5: Recommendation & Decision Engine
        $this->app->singleton(AiRecommendationEngineInterface::class, AiRecommendationEngine::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
