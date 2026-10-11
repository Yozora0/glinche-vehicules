<?php

namespace App\Providers;

use App\Services\Glinche\GlincheClient;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(GlincheClient::class, function () {
            $config = config('services.glinche');

            return new GlincheClient(
                baseUrl: (string) $config['base_url'],
                email: (string) $config['email'],
                password: (string) $config['password'],
                timeout: (int) $config['timeout'],
                tokenTtl: (int) $config['token_ttl'],
            );
        });
    }

    public function boot(): void
    {
    }
}
