<?php

namespace Propaganistas\LaravelFakeId\Illuminate;

use Closure;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Routing\Router as IlluminateRouter;
use Illuminate\Support\Facades\App;
use Propaganistas\LaravelFakeId\FakeIdTrait;

class Router extends IlluminateRouter
{

    /**
     * Register a model binder for a wildcard.
     *
     * @param  string        $key
     * @param  string        $class
     * @param  \Closure|null $callback
     * @return void
     *
     * @throws NotFoundHttpException
     */
    public function model($key, $class, Closure $callback = null)
    {
        $this->bind($key, function ($value) use ($class, $callback) {
            if (is_null($value)) {
                return;
            }

            // For model binders, we will attempt to retrieve the models using the first
            // method on the model instance. If we cannot retrieve the models we'll
            // throw a not found exception otherwise we will return the instance.
            $instance = $this->container->make($class);

            // Decode FakeId first if applicable.
            if (in_array(FakeIdTrait::class, class_uses($class))) {
                $value = App::make('fakeid')->decode($value);
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
    }
}
