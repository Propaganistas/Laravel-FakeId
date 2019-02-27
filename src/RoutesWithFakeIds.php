<?php namespace Propaganistas\LaravelFakeId;

use Illuminate\Support\Facades\App;
use Exception;
use RuntimeException;

trait RoutesWithFakeIds
{
    /**
     * Get the value of the model's route key.
     *
     * @return mixed
     */
    public function getRouteKey()
    {
        $key = $this->getKey();

        if ($this->getKeyType() === 'int' && (ctype_digit($key) || is_int($key))) {
            return App::make('fakeid')->encode((int) $key);
        }

        throw new RuntimeException('Key should be of type int to encode into a fake id.');
    }

    /**
     * Retrieve model for route model binding
     *
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value)
    {
        if (! (ctype_digit($value) || is_int($value))) {
            return null;
        }

        try {
            $value = App::make('fakeid')->decode((int) $value);
        } catch (Exception $e) {
            return null;
        }

        return $this->where($this->getRouteKeyName(), $value)->first();
    }
}
