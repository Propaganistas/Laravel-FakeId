# Laravel FakeID

[![Build Status](https://travis-ci.org/Propaganistas/Laravel-FakeId.svg?branch=master)](https://travis-ci.org/Propaganistas/Laravel-FakeId)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Propaganistas/Laravel-FakeId/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Propaganistas/Laravel-FakeId/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/Propaganistas/Laravel-FakeId/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Propaganistas/Laravel-FakeId/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/propaganistas/laravel-fakeid/v/stable)](https://packagist.org/packages/propaganistas/laravel-fakeid)
[![Total Downloads](https://poser.pugx.org/propaganistas/laravel-fakeid/downloads)](https://packagist.org/packages/propaganistas/laravel-fakeid)
[![License](https://poser.pugx.org/propaganistas/laravel-fakeid/license)](https://packagist.org/packages/propaganistas/laravel-fakeid)

Enables automatic Eloquent model ID obfuscation in routes using [Optimus](https://github.com/jenssegers/optimus).

### Installation

1. Run the Composer require command to install the package

    ```bash
    composer require propaganistas/laravel-fakeid
    ```

2. The package will automatically register itself.

3. Run the following artisan command to auto-initialize the package's settings
    
    ```bash
    php artisan fakeid:setup
    ```

### Usage

First of all, make sure the model is bound to Laravel's Router using the `model()` method, e.g. on top of the `routes.php` file (or in the `boot()` method of `RouteServiceProvider` if you use route caching):

```php
Route::model('mymodel', 'App\MyModel');
```

This way you can reference a placeholder in your routes (`edit/{mymodel}`)

Next, simply import the `RoutesWithFakeIds` trait into your model:

```php
use Illuminate\Database\Eloquent\Model;
use Propaganistas\LaravelFakeId\RoutesWithFakeIds;

class MyModel extends Model {

  use RoutesWithFakeIds;

}
```

All routes generated for this model will now automatically contain obfuscated IDs and incoming requests to `{mymodel}` routes containing obfuscated IDs will be handled correctly.

### Example Usage ###
Assuming a `MyModel` model having a named `show` route.

`routes/web.php`:

```php
Route::get('mymodels/{model}', 'MyModelController@show')->name('mymodels.show');
```

A route to this specific endpoint can be generated using Laravel's `route()` helper.

`MyModelController.php`:

```php
public function show()
{ 
  return view('mymodels.index', [
    'myModels' => MyModel::all()
  ]);
}
```

In the `mymodels.index.blade.php` view:

```php
@foreach ($myModels as $model)
  <a href="{{ route('mymodels.show', $model) }}"> {{ $model->name }} </a>
@endforeach
```


### FAQ

**Why didn't you implement [Hashids](https://github.com/vinkla/hashids) instead of [Optimus](https://github.com/jenssegers/optimus)?**

PERFORMANCE!
Optimus is based on Knuth's multiplicative hashing method and proves to be quite faster than Hashids. It's even mentioned on Hashids' own [website](http://hashids.org/#alternatives).
