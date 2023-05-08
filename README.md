# Laravel FakeID

![Tests](https://github.com/Propaganistas/Laravel-FakeId/workflows/Tests/badge.svg?branch=master)
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

Simply import the `RoutesWithFakeIds` trait into your model:

```php
use Illuminate\Database\Eloquent\Model;
use Propaganistas\LaravelFakeId\RoutesWithFakeIds;

class MyModel extends Model {

  use RoutesWithFakeIds;

}
```

All routes generated for this particular model will expose a **fake** ID instead of the raw primary key. Moreover incoming requests containing those fake IDs are automatically converted back to a real ID. The obfuscation layer is therefore transparent and doesn't require you to rethink everything. Just use Laravel as you normally would.


### Example ###
Assuming an `Article` model having a named `show` route.

`routes/web.php`:

```php
Route::get('articles/{article}', 'ArticleController@show')->name('articles.show');
```

`app/Article.php`

```php
use Illuminate\Database\Eloquent\Model;
use Propaganistas\LaravelFakeId\RoutesWithFakeIds;

class Article extends Model {
  use RoutesWithFakeIds;
}
```

A route to this specific endpoint can now be generated using Laravel's `route()` helper, and it will automatically contain a **fake** ID:


```php
<a href="{{ route('articles.show', $article) }}"> {{ $article->name }} </a>
```


### FAQ

**Why didn't you implement [Hashids](https://github.com/vinkla/hashids) instead of [Optimus](https://github.com/jenssegers/optimus)?**

PERFORMANCE!
Optimus is based on Knuth's multiplicative hashing method and proves to be quite faster than Hashids. It's even mentioned on Hashids' own [website](http://hashids.org/#alternatives).
