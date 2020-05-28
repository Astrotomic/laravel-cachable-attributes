# Laravel cachable Attributes

[![Latest Version](http://img.shields.io/packagist/v/astrotomic/laravel-cachable-attributes.svg?label=Release&style=for-the-badge)](https://packagist.org/packages/astrotomic/laravel-cachable-attributes)
[![MIT License](https://img.shields.io/github/license/Astrotomic/laravel-cachable-attributes.svg?label=License&color=blue&style=for-the-badge)](https://github.com/Astrotomic/laravel-cachable-attributes/blob/master/LICENSE)
[![Offset Earth](https://img.shields.io/badge/Treeware-%F0%9F%8C%B3-green?style=for-the-badge)](https://plant.treeware.earth/Astrotomic/laravel-cachable-attributes)

[![Total Downloads](https://img.shields.io/packagist/dt/astrotomic/laravel-cachable-attributes.svg?label=Downloads&style=flat-square&cacheSeconds=600)](https://packagist.org/packages/astrotomic/laravel-cachable-attributes)
[![GitHub Workflow Status](https://img.shields.io/github/workflow/status/Astrotomic/laravel-cachable-attributes/run-tests?style=flat-square&logoColor=white&logo=github&label=Tests)](https://github.com/Astrotomic/laravel-cachable-attributes/actions?query=workflow%3Arun-tests)
[![StyleCI](https://styleci.io/repos/205167128/shield)](https://styleci.io/repos/205167128)

**If you want to cache your heavy attribute accessors - this package is for you!**

This Laravel package provides a trait to use in your models which provides methods to cache your complex, long running, heavy model accessor results.

## Installation

You just have to run `composer require astrotomic/laravel-cachable-attributes`. There's no ServiceProvider or config or anything else.

## Quick Example

Sometimes you have properties which run addition database queries, do heavy calculations or have to retrieve data from somewhere. This slows down your application and if you access the attribute multiple times the accessor is also executed multiple times.

```php
class Gallery extends Model
{
    public function images(): HasMany
    {
        return $this->hasMany(Image::class, 'gallery_id');
    }

    public function getStorageSizeAttribute(): int
    {
        return $this->images()->sum('file_size');
    }
}
```

This example would run the sum query every time you access `$gallery->storage_size`.
By using the trait you can prevent this.

```php
use Astrotomic\CachableAttributes\CachableAttributes;
use Astrotomic\CachableAttributes\CachesAttributes;

class Gallery extends Model implements CachableAttributes
{
    use CachesAttributes;
    
    protected $cachableAttributes = [
        'storage_size',
    ];

    public function images(): HasMany
    {
        return $this->hasMany(Image::class, 'gallery_id');
    }

    public function getStorageSizeAttribute(): int
    {
        return $this->remember('storage_size', 0, function(): int {
            return $this->images()->sum('file_size');
        });
    }
}
```

This will run the database query only once per request. The ttl of `0` means to cache only for the current runtime. You could also use `null` or `rememberForever()` to remember the value forever (until manually deleted). Or use any positive number to cache for the amount of seconds.
