<?php

namespace Propaganistas\LaravelFakeId;

use Illuminate\Support\ServiceProvider;
use Jenssegers\Optimus\Optimus;
use Propaganistas\LaravelFakeId\Commands\FakeIdSetupCommand;

class FakeIdServiceProvider extends ServiceProvider
{
    /**
     * Boots the service provider.
     *
     * @return void
     */
    public function boot()
    {
        // Publish config.
        $this->publishes([
            __DIR__.'/../config/config.php' => config_path('fakeid.php'),
        ], 'config');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'fakeid');

        $this->registerCommand();
        $this->registerOptimus();
    }

    /**
     * Register the Optimus container.
     *
     * @return void
     */
    protected function registerOptimus()
    {
        $this->app->singleton('Jenssegers\Optimus\Optimus', function ($app) {
            return new Optimus(
                $app['config']['fakeid.prime'],
                $app['config']['fakeid.inverse'],
                $app['config']['fakeid.random']
            );
        });

        $this->app->alias('Jenssegers\Optimus\Optimus', 'optimus');
        $this->app->alias('Jenssegers\Optimus\Optimus', 'fakeid');
    }

    /**
     * Register the Artisan setup command.
     *
     * @return void
     */
    protected function registerCommand()
    {
        $this->app->singleton('fakeid.command.setup', function ($app) {
            return new FakeIdSetupCommand;
        });

        $this->commands('fakeid.command.setup');
    }
}
