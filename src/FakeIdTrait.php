<?php namespace Propaganistas\LaravelFakeId;

use Illuminate\Support\Facades\App;

trait FakeIdTrait
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
}
