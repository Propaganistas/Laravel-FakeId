<?php
namespace Propaganistas\LaravelFakeId\Tests\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Propaganistas\LaravelFakeId\RoutesWithFakeIds;

class Deletable extends Model
{
    use SoftDeletes;
    use RoutesWithFakeIds;
}