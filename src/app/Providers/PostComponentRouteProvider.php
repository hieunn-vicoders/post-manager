<?php

namespace VCComponent\Laravel\Post\Providers;

use Illuminate\Support\ServiceProvider;

class PostComponentRouteProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../../routes/api.php');
        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {

    }
}
