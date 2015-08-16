<?php

return [

	/*
	|--------------------------------------------------------------------------
	| FakeId connection settings
	|--------------------------------------------------------------------------
	|
	| Since FakeId depends on jenssegers/optimus, we need three values:
	| - A large prime number lower than 2147483647
	| - The inverse prime so that (PRIME * INVERSE) & MAXID == 1
	| - A large random integer lower than 2147483647
	|
	*/

	'prime' => env('FAKEID_PRIME', 961748927),
	'inverse' => env('FAKEID_INVERSE', 1430310975),
	'random' => env('FAKEID_RANDOM', 620464665),

	/*
	|--------------------------------------------------------------------------
	| FakeId custom router
	|--------------------------------------------------------------------------
	|
	| FakeId overrides Laravel's Router instance for it's magic to happen
	| automatically. If you have created a custom Router yourself, you can
	| disable the FakeId Router using the config parameter below to prevent
	| interference.
	|
	| In that case, note that you need to decode incoming FakeIDs yourself using:
	|
	|   Route::bind('mymodel', function($value, $route) {
    |      return app('fakeid')->decode($value);
    |   });
	|
	*/

	'enable_router' => true,
];
