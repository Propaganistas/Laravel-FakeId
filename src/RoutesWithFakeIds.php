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

        if ($this->getKeyType() === 'int' && (is_int($key) || ctype_digit($key))) {
            return App::make('fakeid')->encode((int) $key);
        }

        throw new RuntimeException('Key should be of type int to encode into a fake id.');
    }

    /**
     * Retrieve the model for a bound value.
     *
     * @param  \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\Relation  $query
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        if (! (ctype_digit($value) || is_int($value))) {
            return $query;
        }

        try {
            $value = App::make('fakeid')->decode((int) $value);
        } catch (Exception $e) {
            return $query;
        }

        return $query->where($field ?? $this->getRouteKeyName(), $value);
    }
}
