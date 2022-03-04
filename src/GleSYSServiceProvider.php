<?php

namespace Dronki\GleSYS;

use Illuminate\Support\ServiceProvider;

class GleSYSServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'glesys');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'glesys');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        if ($this->app->runningInConsole()) {
            $this->mergeConfigFrom( __DIR__.'/../config/config.php', 'glesys' );
            // Registering package commands.
            // $this->commands([]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        // $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'glesys');

        if( app()->runningInConsole() ) {
            $this->publishes( [
                __DIR__ . '/../config/config.php' => config_path( 'glesys.php' ),
            ], 'glesys' );
        }

        

        // Register the main class to use with the facade
        $this->app->singleton('glesys', function () {
            return new GleSYS;
        });
    }
}
