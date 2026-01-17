<?php

namespace Beartropy\Saml2;

use Illuminate\Support\ServiceProvider;
use Beartropy\Saml2\Services\Saml2Service;
use Beartropy\Saml2\Services\IdpResolver;
use Beartropy\Saml2\Services\MetadataParser;

class Saml2ServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/beartropy-saml2.php',
            'beartropy-saml2'
        );

        // Register services as singletons
        $this->app->singleton(IdpResolver::class, function ($app) {
            return new IdpResolver();
        });

        $this->app->singleton(MetadataParser::class, function ($app) {
            return new MetadataParser();
        });

        $this->app->singleton(Saml2Service::class, function ($app) {
            return new Saml2Service(
                $app->make(IdpResolver::class),
                $app->make(MetadataParser::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/saml2.php');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'beartropy-saml2');

        // Load translations
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'beartropy-saml2');

        // Publish config
        $this->publishes([
            __DIR__ . '/../config/beartropy-saml2.php' => config_path('beartropy-saml2.php'),
        ], 'beartropy-saml2-config');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'beartropy-saml2-migrations');

        // Publish views
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/beartropy-saml2'),
        ], 'beartropy-saml2-views');

        // Publish translations
        $this->publishes([
            __DIR__ . '/../resources/lang' => lang_path('vendor/beartropy-saml2'),
        ], 'beartropy-saml2-lang');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\Commands\CreateIdpCommand::class,
                Console\Commands\ListIdpsCommand::class,
                Console\Commands\TestIdpCommand::class,
                Console\Commands\DeleteIdpCommand::class,
                Console\Commands\GenerateSpCertCommand::class,
                Console\Commands\RefreshIdpMetadataCommand::class,
                Console\Commands\PublishListenerCommand::class,
                Console\Commands\ResetSetupCommand::class,
            ]);
        }
    }
}
