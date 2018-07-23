<?php namespace Propaganistas\LaravelFakeId;

use Illuminate\Support\Facades\App;
use Exception;

trait RoutesWithFakeIds
{
    /**
     * Get the value of the model's route key.
     *
     * @return mixed
     */
    public function getRouteKey()
    {
        return App::make('fakeid')->encode($this->getKey());
    }

    /**
     * Retrieve model for route model binding
     *
     * @param  mixed  $routeKey
     * @return null|\Illuminate\Database\Eloquent\Model
     */
    public function resolveRouteBinding($routeKey)
    {
        try {
            $key = App::make('fakeid')->decode($routeKey);
        } catch (Exception $e) {
            return null;
        }

        return $this->where($this->getRouteKeyName(), $key)->first();
    }
}
