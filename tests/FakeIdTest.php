<?php namespace Propaganistas\LaravelFakeId\Tests;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Support\Facades\Route;
use Orchestra\Testbench\TestCase;
use Propaganistas\LaravelFakeId\Facades\FakeId;
use Propaganistas\LaravelFakeId\Tests\Entities\Fake;
use Propaganistas\LaravelFakeId\Tests\Entities\Real;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FakeIdTest extends TestCase
{
    /**
     * @param \Illuminate\Foundation\Application $application
     * @return array
     */
    protected function getPackageProviders($application)
    {
        return [
            'Propaganistas\LaravelFakeId\FakeIdServiceProvider',
        ];
    }

    public function setUp()
    {
        parent::setUp();

        $this->configureDatabase();

        Route::model('real', 'Propaganistas\LaravelFakeId\Tests\Entities\Real');
        Route::fakeIdModel('fake', 'Propaganistas\LaravelFakeId\Tests\Entities\Fake');

        $middlewareBindings = version_compare($this->app->version(), '5.3.0') >= 0 ? 'Illuminate\Routing\Middleware\SubstituteBindings' : null;

        Route::get('real/{real}', [
            'as' => 'real', function ($real) {
                return 'ID:' . $real->getKey();
            },
            'middleware' => $middlewareBindings,
        ]);

        Route::get('fake/{fake}', [
            'as' => 'fake', function ($fake) {
                return 'ID:' . $fake->getKey();
            },
            'middleware' => $middlewareBindings,
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

    public function testFakeModelIsDecodedCorrectly()
    {
        $model = Fake::create([]);

        $response = $this->call('get', route('fake', ['fake' => $model]));

        $this->assertContains((string) 'ID:' . $model->getKey(), $response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRealModelIsStillDecodedCorrectly()
    {
        $model = Real::create([]);

        $response = $this->call('get', route('real', ['real' => $model]));

        $this->assertContains((string) 'ID:' . $model->getKey(), $response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function testInvalidFakeModelReturnsNotFound()
    {
        $this->app['config']->set('app.debug', false);

        $response = $this->call('get', route('fake', ['fake' => 'foo']));

        if (isset($response->exception)) {
            // Starting from L5.3 these exceptions are silenced, so let's rethrow them.
            throw $response->exception;
        }
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidFakeModelReturnsProperExceptionWhenDebugOn()
    {
        $this->app['config']->set('app.debug', true);

        $response = $this->call('get', route('fake', ['fake' => 'foo']));

        if (isset($response->exception)) {
            // Starting from L5.3 these exceptions are silenced, so let's rethrow them.
            throw $response->exception;
        }
    }


}