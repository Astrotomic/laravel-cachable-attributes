<?php

namespace Astrotomic\CachableAttributes\Tests\Models;

use Astrotomic\CachableAttributes\CachableAttributes;
use Astrotomic\CachableAttributes\CachesAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
