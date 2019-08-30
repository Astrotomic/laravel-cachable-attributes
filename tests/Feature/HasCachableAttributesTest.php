<?php

namespace Astrotomic\CachableAttributes\Tests\Feature;

use Astrotomic\CachableAttributes\Tests\Models\Gallery;
use Astrotomic\CachableAttributes\Tests\TestCase;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use Illuminate\Support\Str;

final class HasCachableAttributesTest extends TestCase
{
    /** @test */
    public function it_returns_cached_value_in_second_run(): void
    {
        $gallery = new class extends Gallery {
            public function getStorageSizeAttribute(): int
            {
                return $this->remember('storage_size', 5, function(): int {
                    return $this->images()->sum('file_size');
                });
            }
        };
        $gallery->name = Str::random();
        $gallery->save();

        $this->assertSame(0, $gallery->storage_size);

        $image = $this->image($gallery, 5);
        $image->save();

        $this->assertSame(5, intval($gallery->images()->sum('file_size')));
        $this->assertSame(0, $gallery->storage_size);

        $gallery->forget('storage_size');

        $this->assertSame(5, $gallery->storage_size);
    }
}
