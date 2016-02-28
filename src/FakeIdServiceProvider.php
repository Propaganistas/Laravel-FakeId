<?php namespace Propaganistas\LaravelFakeId;

use Illuminate\Support\ServiceProvider;
use Jenssegers\Optimus\Optimus;
use Propaganistas\LaravelFakeId\Illuminate\Router;

class FakeIdServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Boots the service provider.
     *
     * @return void
     */
    public function boot()
    {
        // Publish config.
        $this->publishes([
            __DIR__ . '/../config/config.php' => config_path('fakeid.php'),
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // Merge default config.
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'fakeid');

        // Register setup command.
        $this->app->singleton('fakeid.command.setup', function ($app) {
            return new Commands\FakeIdSetupCommand();
        });
        $this->commands('fakeid.command.setup');

        // Register FakeId driver.
        $this->app->singleton('fakeid', function ($app) {
            return new Optimus(
                $app['config']['fakeid.prime'],
                $app['config']['fakeid.inverse'],
                $app['config']['fakeid.random']
            );
        });

        // Register customized router.
        if ($this->app['config']['fakeid.enable_router']) {
            $this->app->extend('router', function ($original, $app) {
                return new Router($app['events'], $app);
            });
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'fakeid',
            'fakeid.command.setup'
        ];
    }

}
