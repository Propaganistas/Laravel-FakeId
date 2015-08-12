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
];
