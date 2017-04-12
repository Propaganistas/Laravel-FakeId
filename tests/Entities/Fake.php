<?php
namespace Propaganistas\LaravelFakeId\Tests\Entities;

use Illuminate\Database\Eloquent\Model;
use Propaganistas\LaravelFakeId\FakeIdTrait;

class Fake extends Model
{
    use FakeIdTrait;
}