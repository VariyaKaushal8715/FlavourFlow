<?php

namespace App\Providers;

use App\Events\OrderPlaced;
use App\Listeners\SendOrderConfirmationNotifications;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $isLocalhost = in_array(request()->getHost(), ['127.0.0.1', 'localhost']);
        if (!$isLocalhost && (app()->environment('production') || request()->header('x-forwarded-proto') === 'https' || str_contains((string) config('app.url'), 'https://'))) {
            URL::forceScheme('https');
        }

        Gate::define('access-admin', fn (User $user): bool => (bool) $user->is_admin);

        Event::listen(
            OrderPlaced::class,
            SendOrderConfirmationNotifications::class
        );
    }
}
