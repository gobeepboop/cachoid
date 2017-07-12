<?php

namespace Beep\Cachoid;

use Illuminate\Support\ServiceProvider;

class CachoidServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(CachoidManager::class, function (): CachoidManager {
            return new CachoidManager($this->app);
        });

        $this->app->bind(RouteBindRegistrar::class, function (): RouteBindRegistrar {
            return new RouteBindRegistrar($this->app['router'], $this->app[CachoidManager::class]);
        });
    }
}
