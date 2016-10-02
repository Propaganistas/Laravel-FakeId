<?php namespace Propaganistas\LaravelFakeId;

use Closure;
use Illuminate\Support\ServiceProvider;
use Jenssegers\Optimus\Optimus;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
            __DIR__ . '/../config/config.php' => config_path('fakeid.php'),
        ], 'config');
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
        $this->app->singleton(Optimus::class, function ($app) {
            return new Optimus(
                $app['config']['fakeid.prime'],
                $app['config']['fakeid.inverse'],
                $app['config']['fakeid.random']
            );
        });
        $this->app->alias(Optimus::class, 'fakeid');

        $this->registerRouterMacro();

    }

    /**
     * Register the custom router macro.
     */
    protected function registerRouterMacro()
    {
        $this->app['router']->macro('fakeIdModel', function ($key, $class, Closure $callback = null) {
            $this->bind($key, function ($value) use ($key, $class, $callback) {
                if (is_null($value)) {
                    return;
                }

                // For model binders, we will attempt to retrieve the models using the first
                // method on the model instance. If we cannot retrieve the models we'll
                // throw a not found exception otherwise we will return the instance.
                $instance = $this->container->make($class);

                // Decode FakeId first if applicable.
                if (in_array(FakeIdTrait::class, class_uses($class))) {
                    $value = $this->container->make('fakeid')->decode($value);
                }

                if ($model = $instance->where($instance->getRouteKeyName(), $value)->first()) {
                    return $model;
                }

                // If a callback was supplied to the method we will call that to determine
                // what we should do when the model is not found. This just gives these
                // developer a little greater flexibility to decide what will happen.
                if ($callback instanceof Closure) {
                    return call_user_func($callback, $value);
                }

                throw new NotFoundHttpException;
            });
        });
    }

}
