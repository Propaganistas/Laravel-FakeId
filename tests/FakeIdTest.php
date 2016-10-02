<?php namespace Propaganistas\LaravelFakeId\Tests;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use Jenssegers\Optimus\Optimus;
use Orchestra\Testbench\TestCase;
use Propaganistas\LaravelFakeId\Facades\FakeId;
use Propaganistas\LaravelFakeId\FakeIdTrait;

class FakeIdTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            'Propaganistas\LaravelFakeId\FakeIdServiceProvider',
        ];
    }

    public function setUp()
    {
        parent::setUp();

        $this->configureDatabase();

        Route::model('real', Real::class);
        Route::fakeIdModel('fake', Fake::class);

        Route::get('real/{real}', ['as' => 'real', function ($real) {
            return 'real';
        }]);

        Route::get('fake/{fake}', ['as' => 'fake', function ($fake) {
            return 'fake';
        }]);
    }

    protected function configureDatabase()
    {
        $db = new DB;
        $db->addConnection([
            'driver'    => 'sqlite',
            'database'  => ':memory:',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ]);
        $db->bootEloquent();
        $db->setAsGlobal();

        DB::schema()->create('reals', function ($table) {
            $table->increments('id');
            $table->timestamps();
        });

        DB::schema()->create('fakes', function ($table) {
            $table->increments('id');
            $table->timestamps();
        });
    }

    public function testFacade()
    {
        $this->assertInstanceOf(Optimus::class, FakeId::getFacadeRoot());
    }

    public function testDefaultModelBindingStillWorks()
    {
        $model = Real::create();

        $expected = url('real/' . $model->getRouteKey());
        $actual = route('real', ['real' => $model]);

        $this->assertEquals($expected, $actual);
    }

    public function testFakeIdTraitEnforcesEncodedRouteKey()
    {
        $model = Fake::create();

        $this->assertNotEquals($model->getRouteKey(), $model->getKey());
        $this->assertEquals($model->getRouteKey(), app('fakeid')->encode($model->getKey()));
    }

    public function testFakeIdModelBindingWorks()
    {
        $model = Fake::create();

        $expected = url('fake/' . $model->getRouteKey());
        $actual = route('fake', ['fake' => $model]);

        $this->assertEquals($expected, $actual);
    }
}

class Real extends Model
{
}

class Fake extends Model
{
    use FakeIdTrait;
}
