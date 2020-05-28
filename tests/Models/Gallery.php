<?php

namespace Astrotomic\CachableAttributes\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Astrotomic\CachableAttributes\CachableAttributes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Astrotomic\CachableAttributes\CachesAttributes;
use Psr\SimpleCache\CacheInterface;

/**
 * @property-read int $storage_size
 */
class Gallery extends Model implements CachableAttributes
{
    use CachesAttributes;

    protected $table = 'galleries';

    protected $cachableAttributes = [
        'test',
        'storage_size',
    ];

    /** @var string[] */
    protected $guarded = [];

    public function images(): HasMany
    {
        return $this->hasMany(Image::class, 'gallery_id');
    }
}
