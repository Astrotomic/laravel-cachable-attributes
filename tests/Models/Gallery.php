<?php

namespace Astrotomic\CachableAttributes\Tests\Models;

use Astrotomic\CachableAttributes\AttributesCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Astrotomic\CachableAttributes\HasCachableAttributes;

/**
 * @property-read int $storage_size
 */
class Gallery extends Model implements AttributesCache
{
    use HasCachableAttributes;

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
