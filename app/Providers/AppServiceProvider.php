<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransportFactory;
use Symfony\Component\Mailer\Transport\Smtp\Stream\SocketStream;

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
        Gate::define('access-admin', fn (User $user): bool => (bool) $user->is_admin);

        $caFile = env('MAIL_CAFILE', storage_path('cacert_with_avg.pem'));
        if (file_exists($caFile)) {
            $this->app->extend('mail.manager', function ($mailManager) use ($caFile) {
                $mailManager->extend('smtp', function (array $config) use ($caFile) {
                    $config['stream'] = array_merge([
                        'ssl' => [
                            'cafile' => $caFile,
                            'verify_peer' => true,
                            'verify_peer_name' => true,
                        ],
                    ], $config['stream'] ?? []);

                    $factory = new EsmtpTransportFactory;
                    $scheme = $config['scheme'] ?? (($config['port'] == 465) ? 'smtps' : 'smtp');

                    $transport = $factory->create(new Dsn(
                        $scheme,
                        $config['host'],
                        $config['username'] ?? null,
                        $config['password'] ?? null,
                        $config['port'] ?? null,
                        $config
                    ));

                    $stream = $transport->getStream();
                    if ($stream instanceof SocketStream) {
                        $stream->setStreamOptions($config['stream']);
                    }

                    return $transport;
                });

                return $mailManager;
            });
        }
    }
}
