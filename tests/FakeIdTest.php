<?php namespace Propaganistas\LaravelFakeId\Tests;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Orchestra\Testbench\TestCase;
use Propaganistas\LaravelFakeId\Facades\FakeId;
use Propaganistas\LaravelFakeId\Tests\Entities\Fake;
use Propaganistas\LaravelFakeId\Tests\Entities\Real;
use RuntimeException;

class FakeIdTest extends TestCase
{
    /**
     * The package providers to register.
     *
     * @param \Illuminate\Foundation\Application $application
     * @return array
     */
    protected function getPackageProviders($application)
    {
        return [
            'Propaganistas\LaravelFakeId\FakeIdServiceProvider',
        ];
    }

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->configureDatabase();

        $this->app['router']->model('real', 'Propaganistas\LaravelFakeId\Tests\Entities\Real');
        $this->app['router']->model('fake', 'Propaganistas\LaravelFakeId\Tests\Entities\Fake');
        
        $this->app['router']->get('real/{real}', [
            'as' => 'real', function ($real) {
                return 'ID:' . $real->getKey();
            },
            'middleware' => 'Illuminate\Routing\Middleware\SubstituteBindings',
        ]);

        $this->app['router']->get('fake/{fake}', [
            'as' => 'fake', function ($fake) {
                return 'ID:' . $fake->getKey();
            },
            'middleware' => 'Illuminate\Routing\Middleware\SubstituteBindings',
        ]);
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

    /**
     * @test
     */
    public function it_can_resolve_the_facade()
    {
        $this->assertInstanceOf('Jenssegers\Optimus\Optimus', FakeId::getFacadeRoot());
    }

    /**
     * @test
     */
    public function it_yields_an_encoded_route_key()
    {
        $model = Fake::create([]);

        $this->assertNotEquals($model->getRouteKey(), $model->getKey());
        $this->assertEquals($model->getRouteKey(), app('fakeid')->encode($model->getKey()));
    }

    /**
     * @test
     */
    public function it_uses_the_encoded_route_key()
    {
        $model = Fake::create([]);

        $expected = url('fake/' . $model->getRouteKey());
        $actual = route('fake', ['fake' => $model]);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function it_finds_the_model_for_an_encoded_route_key()
    {
        $model = Fake::create([]);

        $response = $this->call('get', route('fake', ['fake' => $model]));

        $this->assertContains((string) 'ID:' . $model->getKey(), $response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_throws_notfound_exception_when_no_model_was_found()
    {
        $response = $this->call('get', route('fake', ['fake' => '123']));

        $this->assertNotNull($response->exception);
        $this->assertEquals(ModelNotFoundException::class, get_class($response->exception));
    }

    /**
     * @test
     */
    public function it_throws_notfound_exception_on_invalid_encoded_route_key()
    {
        $response = $this->call('get', route('fake', ['fake' => 'foo']));

        $this->assertNotNull($response->exception);
        $this->assertEquals(ModelNotFoundException::class, get_class($response->exception));
    }

    /**
     * @test
     */
    public function it_still_yields_a_regular_key_for_regular_models()
    {
        $model = Real::create([]);

        $expected = url('real/' . $model->getRouteKey());
        $actual = route('real', ['real' => $model]);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function it_still_finds_the_model_for_a_regular_route_key()
    {
        $model = Real::create([]);

        $response = $this->call('get', route('real', ['real' => $model]));

        $this->assertContains((string) 'ID:' . $model->getKey(), $response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_doesnt_throw_on_stringified_int_key()
    {
    	$model = Fake::create([]);
    	$model->id = '123';

    	$this->assertEquals($model->getKey(), 123);
    	$this->assertEquals($model->getRouteKey(), app('fakeid')->encode($model->getKey()));
    }

    /**
     * @test
     */
    public function it_throws_runtime_exception_on_non_int_key()
    {
    	$model = Fake::create([]);
    	$model->setKeyType('string');

    	$this->expectException(RuntimeException::class);
    	$this->assertEquals($model->getRouteKey(), app('fakeid')->encode($model->getKey()));
    }
}