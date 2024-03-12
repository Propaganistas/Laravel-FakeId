<?php

namespace Propaganistas\LaravelFakeId\Tests;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Propaganistas\LaravelFakeId\Facades\FakeId;
use Propaganistas\LaravelFakeId\FakeIdServiceProvider;
use Propaganistas\LaravelFakeId\Tests\Entities\Deletable;
use Propaganistas\LaravelFakeId\Tests\Entities\Fake;
use Propaganistas\LaravelFakeId\Tests\Entities\FakeWithRouteKeyName;
use Propaganistas\LaravelFakeId\Tests\Entities\Real;

class FakeIdTest extends TestCase
{
    protected function getPackageProviders($application)
    {
        return [
            FakeIdServiceProvider::class,
        ];
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->configureDatabase();
    }

    protected function configureDatabase()
    {
        $db = new DB;
        $db->addConnection([
            'driver' => 'sqlite',
            'database' => ':memory:',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
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

        DB::schema()->create('deletables', function ($table) {
            $table->increments('id');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    protected function createRoute($path, $handler)
    {
        return $this->app['router']->get($path, [
            'middleware' => SubstituteBindings::class,
            'uses' => $handler,
        ]);
    }

    #[Test]
    public function it_resolves_the_facade()
    {
        $this->assertInstanceOf('Jenssegers\Optimus\Optimus', FakeId::getFacadeRoot());
    }

    #[Test]
    public function it_encodes_the_route_key()
    {
        $model = Fake::create();

        $this->assertNotEquals($model->getRouteKey(), $model->getKey());
        $this->assertEquals($model->getRouteKey(), app('fakeid')->encode($model->getKey()));
    }

    #[Test]
    public function it_decodes_the_route_key_when_resolving()
    {
        $model = Fake::create();

        $query = $model->resolveRouteBindingQuery(Fake::query(), $model->getRouteKey());

        $this->assertNotEquals($model->getRouteKey(), $model->getKey());
        $this->assertEquals('select * from "fakes" where "id" = ?', $query->toSql());
        $this->assertEquals([$model->getKey()], $query->getBindings());
    }

    #[Test]
    public function it_decodes_the_route_key_when_resolving_with_the_custom_route_key_name()
    {
        $model = FakeWithRouteKeyName::create();

        $query = $model->resolveRouteBindingQuery(Fake::query(), $model->getRouteKey());

        $this->assertNotEquals($model->getRouteKey(), $model->getKey());
        $this->assertEquals('select * from "fakes" where "foo" = ?', $query->toSql());
        $this->assertEquals([$model->getKey()], $query->getBindings());
    }

    #[Test]
    public function it_decodes_the_route_key_when_resolving_with_a_custom_attribute()
    {
        $model = Fake::create();

        $query = $model->resolveRouteBindingQuery(Fake::query(), $model->getRouteKey(), 'foo');

        $this->assertNotEquals($model->getRouteKey(), $model->getKey());
        $this->assertEquals('select * from "fakes" where "foo" = ?', $query->toSql());
        $this->assertEquals([$model->getKey()], $query->getBindings());
    }

    #[Test]
    public function it_doesnt_throw_when_resolving_an_undecodable_route_key()
    {
        $model = Fake::create();

        $query = $model->resolveRouteBindingQuery(Fake::query(), 'abc');

        $this->assertNotEquals($model->getRouteKey(), $model->getKey());
        $this->assertEquals('select * from "fakes" where "id" = ?', $query->toSql());
        $this->assertEquals(['abc'], $query->getBindings());
    }

    #[Test]
    public function it_resolves_implicit_bindings()
    {
        $this->createRoute('fake/{fake}', function (Fake $fake) {
            return "ID:{$fake->getKey()}";
        });

        $model = Fake::create();

        $response = $this->get("fake/{$model->getRouteKey()}");

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("ID:{$model->getKey()}", $response->getContent());
    }

    #[Test]
    public function it_resolves_implicit_bindings_with_trashed()
    {
        $this->createRoute('fake/{deletable}', function (Deletable $deletable) {
            return "ID:{$deletable->getKey()}";
        })->withTrashed();

        $model = Deletable::create();
        $model->delete();

        $response = $this->get("fake/{$model->getRouteKey()}");

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("ID:{$model->getKey()}", $response->getContent());
    }

    #[Test]
    public function it_resolves_explicit_bindings()
    {
        Route::model('fake', Fake::class);

        $this->createRoute('fake/{fake}', function (Fake $fake) {
            return "ID:{$fake->getKey()}";
        });

        $model = Fake::create();

        $response = $this->get("fake/{$model->getRouteKey()}");

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("ID:{$model->getKey()}", $response->getContent());
    }

    /**
     * Explicit model bindings completely omit a model's route resolution logic.
     * `$route->withTrashed()` only works for implicit bindings, so it won't
     * help us here. There's no real way to support this feature properly.
     *
     * This test solely exists to remind us all of that :-)
     *
     * Or if Laravel implements `withTrashed()` support for explicit
     * bindings some time, it will notify us by simply failing.
     *
     * See next test for a working explicit binding callback.
     *
     * @test
     */
    public function it_cannot_resolve_soft_deleted_explicit_bindings_with_trashed()
    {
        Route::model('deletable', Deletable::class);

        $this->createRoute('fake/{deletable}', function (Deletable $deletable) {
            return "ID:{$deletable->getKey()}";
        })->withTrashed(); // Has NO effect for explicit bindings.

        $model = Deletable::create();
        $model->delete();

        $response = $this->get("fake/{$model->getRouteKey()}");

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertNotNull($response->exception);
        $this->assertEquals(ModelNotFoundException::class, get_class($response->exception));
    }

    /**
     * This test solely exists to provide a working boilerplate
     * to showcase how explicit bindings could be configured
     * to properly work with soft-deleted models.
     *
     * @test
     */
    public function it_resolves_soft_deleted_explicit_bindings_with_trashed_with_working_callback()
    {
        // This is the important part.
        Route::model('deletable', Deletable::class, function ($value) {
            $query = Deletable::query()->withTrashed();

            return (new Deletable)->resolveRouteBindingQuery($query, $value)->firstOrFail();
        });

        $this->createRoute('fake/{deletable}', function (Deletable $deletable) {
            return "ID:{$deletable->getKey()}";
        })->withTrashed(); // Has NO effect for explicit bindings.

        $model = Deletable::create();
        $model->delete();

        $response = $this->get("fake/{$model->getRouteKey()}");

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("ID:{$model->getKey()}", $response->getContent());
    }

    #[Test]
    public function it_returns_notfound_on_model_not_found()
    {
        $this->createRoute('fake/{fake}', function (Fake $fake) {
            return "ID:{$fake->getKey()}";
        });

        $model = Fake::create();
        $model->delete();

        $response = $this->get("fake/{$model->getRouteKey()}");

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertNotNull($response->exception);
        $this->assertEquals(ModelNotFoundException::class, get_class($response->exception));
    }

    #[Test]
    public function it_returns_notfound_on_undecodable_route_key()
    {
        $this->createRoute('fake/{fake}', function (Fake $fake) {
            return "ID:{$fake->getKey()}";
        });

        $response = $this->get('fake/foo');

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertNotNull($response->exception);
        $this->assertEquals(ModelNotFoundException::class, get_class($response->exception));
    }
}
