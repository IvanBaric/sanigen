<?php

namespace IvanBaric\Sanigen;

use Illuminate\Support\ServiceProvider;

/**
 * Service provider for the Sanigen Model Tools package.
 * 
 * This service provider registers the package's configuration file
 * and makes it available to the Laravel application.
 */
class SanigenServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     * 
     * This method is called after all other service providers have been registered,
     * meaning you have access to all other services that have been registered by the framework.
     * 
     * @return void
     */
    public function boot(): void
    {
        // Publish the configuration file to the application's config directory
        $this->publishes([
            __DIR__ . '/../config/sanigen.php' => config_path('sanigen.php'),
        ], 'config');
    }

    /**
     * Register any package services.
     * 
     * This method is called before the boot method and before any other service provider has
     * been registered. The default service providers are registered before the boot method is called.
     * 
     * @return void
     */
    public function register(): void
    {
        // Merge the package's configuration file with the application's published copy
        $this->mergeConfigFrom(
            __DIR__ . '/../config/sanigen.php',
            'sanigen'
        );
    }
}
