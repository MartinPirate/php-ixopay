<?php

namespace Ixopay\Client\Laravel;

use Ixopay\Client\Client;
use Psr\Log\LoggerInterface;
use Illuminate\Support\ServiceProvider;
use Ixopay\Client\Laravel\Commands\InstallCommand;

class IxopayServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/ixopay.php', 'ixopay');

        $this->app->singleton('ixopay.manager', function ($app) {
            $logger = $app->bound(LoggerInterface::class)
                ? $app->make(LoggerInterface::class)
                : null;

            return new IxopayManager($app['config'], $logger);
        });

        $this->app->singleton('ixopay.client', fn ($app) => $app->make('ixopay.manager')->client());
        $this->app->singleton(CallbackRequest::class, fn ($app) => new CallbackRequest($app->make(Client::class)));

        $this->app->alias('ixopay.manager', IxopayManager::class);
        $this->app->alias('ixopay.client', Client::class);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/ixopay.php' => $this->app->configPath('ixopay.php'),
        ], 'ixopay-config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
            ]);
        }
    }
}
