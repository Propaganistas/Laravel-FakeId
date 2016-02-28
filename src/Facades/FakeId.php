<?php namespace Propaganistas\LaravelFakeId\Facades;

use Illuminate\Support\Facades\Facade;

class FakeId extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    protected static function getFacadeAccessor()
    {
        return 'fakeid';
    }

}
