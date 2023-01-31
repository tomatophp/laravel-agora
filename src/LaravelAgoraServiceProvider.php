<?php

namespace TomatoPHP\LaravelAgora;

use Illuminate\Support\ServiceProvider;


class LaravelAgoraServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //Register Config file
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-agora.php', 'laravel-agora');

        //Publish Config
        $this->publishes([
           __DIR__.'/../config/laravel-agora.php' => config_path('laravel-agora.php'),
        ], 'laravel-agora-config');

    }

    public function boot(): void
    {
        //you boot methods here
    }
}
