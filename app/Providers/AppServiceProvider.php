<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\MqttConnectionService;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(MqttConnectionService::class, function ($app) {
            return MqttConnectionService::getInstance();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
