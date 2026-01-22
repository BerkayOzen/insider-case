<?php

namespace App\Providers;

use App\Support\Random\PhpRandomGenerator;
use App\Support\Random\RandomGenerator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(RandomGenerator::class, PhpRandomGenerator::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
