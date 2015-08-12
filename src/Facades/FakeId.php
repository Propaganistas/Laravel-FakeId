<?php namespace Propaganistas\LaravelFakeId\Facades;

use Illuminate\Support\Facades\Facade;

class FakeId extends Facade {

	protected static function getFacadeAccessor() { return 'fakeid'; }

}
