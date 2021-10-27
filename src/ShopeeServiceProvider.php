<?php

namespace Laraditz\Shopee;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laraditz\Shopee\Console;

class ShopeeServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'shopee');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'shopee');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->registerRoutes();


        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('shopee.php'),
            ], 'config');

            // Publishing the views.
            /*$this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/shopee'),
            ], 'views');*/

            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/shopee'),
            ], 'assets');*/

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/shopee'),
            ], 'lang');*/

            // Register the command if we are using the application via the CLI
            if ($this->app->runningInConsole()) {
                $this->commands([
                    Console\RefreshTokenCommand::class,
                    Console\FlushExpiredTokenCommand::class,
                ]);
            }
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'shopee');

        // Register the main class to use with the facade
        $this->app->singleton('shopee', function () {
            return new Shopee;
        });
    }

    protected function registerRoutes()
    {
        Route::group($this->routeConfiguration(), function () {
            Route::name('shopee.')->group(function () {
                $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
            });
        });
    }

    protected function routeConfiguration()
    {
        return [
            'prefix' => config('shopee.routes.prefix'),
            'middleware' => config('shopee.middleware'),
        ];
    }
}
