<?php

namespace App\Http\Controllers\Admin;

use App\AI\Adapters\FlavourFlowAdapter;
use App\AI\Contracts\AiAdapterInterface;
use App\AI\Contracts\AiEngineInterface;
use App\AI\Contracts\AiRequestInterface;
use App\AI\Contracts\AiResponseInterface;
use App\AI\Core\AiEngine;
use App\AI\Core\AiRequest;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View as ViewFactory;
use Throwable;

class AdminAiController extends Controller
{
    public function index(): View
    {
        Gate::authorize('access-admin');

        $checks = [
            'core_loads' => $this->checkCoreLoads(),
            'contracts_resolve' => $this->checkContractsResolve(),
            'adapter_loads' => $this->checkAdapterLoads(),
            'files_exist' => $this->checkRequiredFilesExist(),
            'config_valid' => $this->checkConfigurationValid(),
            'container_resolves' => $this->checkContainerServicesResolve(),
            'routes_views_work' => $this->checkRoutesAndViewsWork(),
            'components_complete' => $this->checkComponentCompleteness(),
        ];

        $allPassed = collect($checks)->every(fn (array $check): bool => $check['passed'] === true);

        return view('admin.ai.index', [
            'checks' => $checks,
            'isReady' => $allPassed,
        ]);
    }

    /**
     * @return array{name: string, description: string, passed: bool, details: string}
     */
    private function checkCoreLoads(): array
    {
        try {
            $classExists = class_exists(AiEngine::class);
            if (! $classExists) {
                return [
                    'name' => 'AI Core Class Load',
                    'description' => 'Verifies App\AI\Core\AiEngine class exists and can be loaded.',
                    'passed' => false,
                    'details' => 'Class App\AI\Core\AiEngine not found.',
                ];
            }

            return [
                'name' => 'AI Core Class Load',
                'description' => 'Verifies App\AI\Core\AiEngine class exists and can be loaded.',
                'passed' => true,
                'details' => 'Class App\AI\Core\AiEngine loaded successfully.',
            ];
        } catch (Throwable $e) {
            return [
                'name' => 'AI Core Class Load',
                'description' => 'Verifies App\AI\Core\AiEngine class exists and can be loaded.',
                'passed' => false,
                'details' => 'Error: '.$e->getMessage(),
            ];
        }
    }

    /**
     * @return array{name: string, description: string, passed: bool, details: string}
     */
    private function checkContractsResolve(): array
    {
        $contracts = [
            AiEngineInterface::class,
            AiRequestInterface::class,
            AiResponseInterface::class,
            AiAdapterInterface::class,
        ];

        $missing = [];
        foreach ($contracts as $contract) {
            if (! interface_exists($contract)) {
                $missing[] = class_basename($contract);
            }
        }

        if ($missing !== []) {
            return [
                'name' => 'Contracts & Interfaces',
                'description' => 'Verifies all AI engine contracts and interfaces exist.',
                'passed' => false,
                'details' => 'Missing contracts: '.implode(', ', $missing),
            ];
        }

        return [
            'name' => 'Contracts & Interfaces',
            'description' => 'Verifies all AI engine contracts and interfaces exist.',
            'passed' => true,
            'details' => count($contracts).' core contracts verified (AiEngineInterface, AiRequestInterface, AiResponseInterface, AiAdapterInterface).',
        ];
    }

    /**
     * @return array{name: string, description: string, passed: bool, details: string}
     */
    private function checkAdapterLoads(): array
    {
        try {
            $adapterClass = config('ai.adapters.flavourflow', FlavourFlowAdapter::class);

            if (! class_exists($adapterClass)) {
                return [
                    'name' => 'FlavourFlow Adapter',
                    'description' => 'Verifies FlavourFlow domain adapter loads and implements AiAdapterInterface.',
                    'passed' => false,
                    'details' => "Adapter class '{$adapterClass}' not found.",
                ];
            }

            /** @var AiAdapterInterface $adapter */
            $adapter = new $adapterClass;
            $isConnected = $adapter->isConnected();

            if (! $isConnected) {
                return [
                    'name' => 'FlavourFlow Adapter',
                    'description' => 'Verifies FlavourFlow domain adapter loads and implements AiAdapterInterface.',
                    'passed' => false,
                    'details' => "Adapter '{$adapter->getName()}' loaded but isConnected() returned false.",
                ];
            }

            return [
                'name' => 'FlavourFlow Adapter',
                'description' => 'Verifies FlavourFlow domain adapter loads and implements AiAdapterInterface.',
                'passed' => true,
                'details' => "Adapter '{$adapter->getName()}' connected successfully.",
            ];
        } catch (Throwable $e) {
            return [
                'name' => 'FlavourFlow Adapter',
                'description' => 'Verifies FlavourFlow domain adapter loads and implements AiAdapterInterface.',
                'passed' => false,
                'details' => 'Error: '.$e->getMessage(),
            ];
        }
    }

    /**
     * @return array{name: string, description: string, passed: bool, details: string}
     */
    private function checkRequiredFilesExist(): array
    {
        $files = [
            'config/ai.php' => config_path('ai.php'),
            'app/Providers/AiServiceProvider.php' => app_path('Providers/AiServiceProvider.php'),
            'app/AI/Contracts/AiEngineInterface.php' => app_path('AI/Contracts/AiEngineInterface.php'),
            'app/AI/Contracts/AiRequestInterface.php' => app_path('AI/Contracts/AiRequestInterface.php'),
            'app/AI/Contracts/AiResponseInterface.php' => app_path('AI/Contracts/AiResponseInterface.php'),
            'app/AI/Contracts/AiAdapterInterface.php' => app_path('AI/Contracts/AiAdapterInterface.php'),
            'app/AI/Core/AiEngine.php' => app_path('AI/Core/AiEngine.php'),
            'app/AI/Core/AiRequest.php' => app_path('AI/Core/AiRequest.php'),
            'app/AI/Core/AiResponse.php' => app_path('AI/Core/AiResponse.php'),
            'app/AI/Adapters/FlavourFlowAdapter.php' => app_path('AI/Adapters/FlavourFlowAdapter.php'),
        ];

        $missing = [];
        foreach ($files as $name => $path) {
            if (! file_exists($path)) {
                $missing[] = $name;
            }
        }

        if ($missing !== []) {
            return [
                'name' => 'Required AI Files',
                'description' => 'Verifies all required AI Core files and directory structures exist on disk.',
                'passed' => false,
                'details' => 'Missing files: '.implode(', ', $missing),
            ];
        }

        return [
            'name' => 'Required AI Files',
            'description' => 'Verifies all required AI Core files and directory structures exist on disk.',
            'passed' => true,
            'details' => count($files).' required AI files verified on disk.',
        ];
    }

    /**
     * @return array{name: string, description: string, passed: bool, details: string}
     */
    private function checkConfigurationValid(): array
    {
        $config = config('ai');

        if (! is_array($config) || $config === []) {
            return [
                'name' => 'Configuration Validity',
                'description' => 'Verifies config/ai.php provides valid configuration settings.',
                'passed' => false,
                'details' => 'Configuration config("ai") is empty or not an array.',
            ];
        }

        $requiredKeys = ['enabled', 'mode', 'default_adapter', 'adapters', 'core'];
        $missingKeys = [];
        foreach ($requiredKeys as $key) {
            if (! array_key_exists($key, $config)) {
                $missingKeys[] = $key;
            }
        }

        if ($missingKeys !== []) {
            return [
                'name' => 'Configuration Validity',
                'description' => 'Verifies config/ai.php provides valid configuration settings.',
                'passed' => false,
                'details' => 'Missing config keys: '.implode(', ', $missingKeys),
            ];
        }

        return [
            'name' => 'Configuration Validity',
            'description' => 'Verifies config/ai.php provides valid configuration settings.',
            'passed' => true,
            'details' => 'Valid config loaded (mode: '.($config['mode'] ?? 'dev').', default adapter: '.($config['default_adapter'] ?? 'none').').',
        ];
    }

    /**
     * @return array{name: string, description: string, passed: bool, details: string}
     */
    private function checkContainerServicesResolve(): array
    {
        try {
            if (! app()->bound(AiEngineInterface::class)) {
                return [
                    'name' => 'Laravel Container Services',
                    'description' => 'Resolves AiEngineInterface and AiAdapterInterface from Laravel service container.',
                    'passed' => false,
                    'details' => 'AiEngineInterface is not bound in service container.',
                ];
            }

            /** @var AiEngineInterface $engine */
            $engine = app(AiEngineInterface::class);

            // Test execution through container-bound engine
            $testRequest = new AiRequest('health_check', ['ping' => 'pong']);
            $response = $engine->process($testRequest);

            if (! $response->isSuccess()) {
                return [
                    'name' => 'Laravel Container Services',
                    'description' => 'Resolves AiEngineInterface and AiAdapterInterface from Laravel service container.',
                    'passed' => false,
                    'details' => 'Engine process test returned non-success status.',
                ];
            }

            return [
                'name' => 'Laravel Container Services',
                'description' => 'Resolves AiEngineInterface and AiAdapterInterface from Laravel service container.',
                'passed' => true,
                'details' => 'Resolved '.get_class($engine).' with '.get_class($engine->getAdapter()).' successfully.',
            ];
        } catch (Throwable $e) {
            return [
                'name' => 'Laravel Container Services',
                'description' => 'Resolves AiEngineInterface and AiAdapterInterface from Laravel service container.',
                'passed' => false,
                'details' => 'Resolution error: '.$e->getMessage(),
            ];
        }
    }

    /**
     * @return array{name: string, description: string, passed: bool, details: string}
     */
    private function checkRoutesAndViewsWork(): array
    {
        $routeExists = Route::has('admin.ai.index');
        $viewExists = ViewFactory::exists('admin.ai.index');

        if (! $routeExists || ! $viewExists) {
            $details = [];
            if (! $routeExists) {
                $details[] = "Route 'admin.ai.index' not registered";
            }
            if (! $viewExists) {
                $details[] = "View 'admin.ai.index' not found";
            }

            return [
                'name' => 'Routes & Views Resolution',
                'description' => 'Verifies admin AI diagnostic routes and blade views resolve.',
                'passed' => false,
                'details' => implode(', ', $details).'.',
            ];
        }

        return [
            'name' => 'Routes & Views Resolution',
            'description' => 'Verifies admin AI diagnostic routes and blade views resolve.',
            'passed' => true,
            'details' => "Route 'admin.ai.index' and view 'admin.ai.index' resolved successfully.",
        ];
    }

    /**
     * @return array{name: string, description: string, passed: bool, details: string}
     */
    private function checkComponentCompleteness(): array
    {
        try {
            /** @var AiEngineInterface $engine */
            $engine = app(AiEngineInterface::class);

            if (! $engine->isReady()) {
                return [
                    'name' => 'AI Engine Component Completeness',
                    'description' => 'Verifies all required components are connected and AI Engine is ready.',
                    'passed' => false,
                    'details' => 'AiEngine::isReady() returned false.',
                ];
            }

            return [
                'name' => 'AI Engine Component Completeness',
                'description' => 'Verifies all required components are connected and AI Engine is ready.',
                'passed' => true,
                'details' => 'All AI engine core components connected and ready.',
            ];
        } catch (Throwable $e) {
            return [
                'name' => 'AI Engine Component Completeness',
                'description' => 'Verifies all required components are connected and AI Engine is ready.',
                'passed' => false,
                'details' => 'Error: '.$e->getMessage(),
            ];
        }
    }
}
