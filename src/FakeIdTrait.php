<?php namespace Propaganistas\LaravelFakeId;

trait FakeIdTrait {

	/**
	 * Get the value of the model's route key.
	 *
	 * @return mixed
	 */
	public function getRouteKey()
	{
	    return app('fakeid')->encode($this->getKey());
	}

}
