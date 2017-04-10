<?php namespace Propaganistas\LaravelFakeId\Tests;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Orchestra\Testbench\TestCase;
use Propaganistas\LaravelFakeId\Facades\FakeId;
use Propaganistas\LaravelFakeId\Tests\Entities\Fake;
use Propaganistas\LaravelFakeId\Tests\Entities\Real;

class FakeIdTest extends TestCase
{
    protected $route;

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();

        $this->configureDatabase();

        $this->route = $this->app['router'];

        $this->route->model('real', Real::class);
        $this->route->fakeIdModel('fake', Fake::class);

        $this->route->get('real/{real}', ['as' => 'real', function ($real) {
            return $real->id;
        }])->middleware(SubstituteBindings::class);

        $this->route->get('fake/{fake}', ['as' => 'fake', function ($fake) {
            return $fake->id;
        }])->middleware(SubstituteBindings::class);

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
        $this->assertInstanceOf('Jenssegers\Optimus\Optimus', FakeId::getFacadeRoot());
    }

    public function testDefaultModelBindingStillWorks()
    {
        $model = Real::create([]);

        $expected = url('real/' . $model->getRouteKey());
        $actual = route('real', ['real' => $model]);

        $this->assertEquals($expected, $actual);
    }

    public function testFakeIdTraitEnforcesEncodedRouteKey()
    {
        $model = Fake::create([]);

        $this->assertNotEquals($model->getRouteKey(), $model->getKey());
        $this->assertEquals($model->getRouteKey(), app('fakeid')->encode($model->getKey()));
    }

    public function testFakeIdModelBindingWorks()
    {
        $model = Fake::create([]);

        $expected = url('fake/' . $model->getRouteKey());
        $actual = route('fake', ['fake' => $model]);

        $this->assertEquals($expected, $actual);
    }

    public function testResponseNotFoundWhenDecodeFailAndDebugOff()
    {
        $this->app['config']->set('app.debug', false);

        $this->get(route('fake', ['fake' => 'not-number']))
            ->assertStatus(404);
    }

    public function testResponseErrorWhenDecodeFailDebugOn()
    {
        $this->app['config']->set('app.debug', true);

        $this->get(route('fake', ['fake' => 'not-number']))
            ->assertStatus(500);

        $this->app['config']->set('app.debug', false);
    }

    public function testResponseFineWhenPassFakeModel()
    {
        $model = Fake::create([]);

        $this->get(route('fake', ['fake' => $model]))
            ->assertSee((string)$model->id)
            ->assertStatus(200);
    }

    public function testResponseFineWhenPassNormalModel()
    {
        $model = Real::create([]);

        $this->get(route('real', ['real' => $model]))
            ->assertSee((string)$model->id)
            ->assertStatus(200);
    }

    public function testResponseFailWhenPassModelId()
    {
        $model = Fake::create([]);

        $this->get(route('fake', ['fake' => $model->id]))
            ->assertStatus(404);
    }

    protected function getPackageProviders($app)
    {
        return [
            'Propaganistas\LaravelFakeId\FakeIdServiceProvider',
        ];
    }
}