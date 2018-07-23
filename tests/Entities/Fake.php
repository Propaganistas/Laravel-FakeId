<?php
namespace Propaganistas\LaravelFakeId\Tests\Entities;

use Illuminate\Database\Eloquent\Model;
use Propaganistas\LaravelFakeId\RoutesWithFakeIds;

class Fake extends Model
{
    use RoutesWithFakeIds;
}