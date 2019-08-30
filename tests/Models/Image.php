<?php

namespace Astrotomic\CachableAttributes\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Image extends Model
{
    /** @var string[] */
    protected $guarded = [];

    public function gallery(): BelongsTo
    {
        return $this->belongsTo(Gallery::class);
    }
}
