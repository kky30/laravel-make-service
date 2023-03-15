<?php

namespace Kky30\LaravelMakeService;

use Illuminate\Support\ServiceProvider;

class MakeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->commands(MakeService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
