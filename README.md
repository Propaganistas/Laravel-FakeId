# Laravel FakeID

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Propaganistas/Laravel-FakeId/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Propaganistas/Laravel-FakeId/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/propaganistas/laravel-fakeid/v/stable)](https://packagist.org/packages/propaganistas/laravel-fakeid)
[![Total Downloads](https://poser.pugx.org/propaganistas/laravel-fakeid/downloads)](https://packagist.org/packages/propaganistas/laravel-fakeid)
[![License](https://poser.pugx.org/propaganistas/laravel-fakeid/license)](https://packagist.org/packages/propaganistas/laravel-fakeid)

Enables automatic Eloquent model ID obfuscation in routes using [Optimus](https://github.com/jenssegers/optimus).

### Installation

1. In the `require` key of `composer.json` file add the following

    ```json
    "propaganistas/laravel-fakeid": "~1.0"
    ```

2. Run the Composer update command

    ```bash
    composer update
    ```

3. In your app config, add the Service Provider to the end of the `$providers` array

   **Laravel 5**
     ```php
    'providers' => [
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
        ...
        Propaganistas\LaravelFakeId\FakeIdServiceProvider::class,
    ],
    ```

4. Run the following artisan command to auto-initialize the package's settings
    
    ```bash
    php artisan fakeid:setup
    ```

### Usage

First of all, make sure the model is bound to Laravel's Router using the `fakeIdModel()` method (e.g. on top of the `routes.php` file):

```php
Route::fakeIdModel('mymodel', 'App\MyModel');
```

This way you can reference a placeholder in your routes (`edit/{mymodel}`)

Next, simply import `Propaganistas\LaravelFakeId\FakeIdTrait` into your model:

```php
use Illuminate\Database\Eloquent\Model;
use Propaganistas\LaravalFakeId\FakeIdTrait;

class MyModel extends Model {

  use FakeIdTrait;

}
```

All routes generated for this model will now automatically contain obfuscated IDs and incoming requests to `{mymodel}` routes containing obfuscated IDs will be handled correctly.

### FAQ

**Why didn't you implement [Hashids](https://github.com/vinkla/hashids) instead of [Optimus](https://github.com/jenssegers/optimus)?**

Simple: speed!
Optimus is based on Knuth's multiplicative hashing method and proves to be quite faster than Hashids. It's even mentioned on Hashids' own [website](http://hashids.org).
