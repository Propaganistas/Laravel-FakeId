<?php
namespace Propaganistas\LaravelFakeId\Tests\Entities;

use Illuminate\Database\Eloquent\Model;
use Propaganistas\LaravelFakeId\RoutesWithFakeIds;

class FakeWithRouteKeyName extends Model
{
    use RoutesWithFakeIds;

    protected $table = "fakes";

    public function getRouteKeyName()
    {
        return 'foo';
    }
}