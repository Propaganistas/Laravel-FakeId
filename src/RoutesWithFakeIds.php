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
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value)
    {
        try {
            $value = App::make('fakeid')->decode($value);
        } catch (Exception $e) {
            return null;
        }

        return $this->where($this->getRouteKeyName(), $value)->first();
    }
}
