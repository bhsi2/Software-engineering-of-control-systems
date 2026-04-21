<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        $this->app->bind(AuthServiceClient::class, function ($app) {
            return new AuthServiceClient(
                new Client(['timeout' => 5.0]),
                config('services.auth_service.url')
            );
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
