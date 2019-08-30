<?php

namespace Astrotomic\CachableAttributes\Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Astrotomic\CachableAttributes\Tests\TestCase;
use Astrotomic\CachableAttributes\Tests\Models\Gallery;

final class HasCachableAttributesTest extends TestCase
{
    /** @test */
    public function it_returns_cached_value_in_second_run(): void
    {
        $gallery = new class extends Gallery {
            public function getStorageSizeAttribute(): int
            {
                return $this->remember('storage_size', 5, function (): int {
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
    /** @test */
    public function it_flushes_all_cached_attributes_on_delete(): void
    {
        $gallery = new class extends Gallery {
            public function getStorageSizeAttribute(): int
            {
                return $this->rememberForever('storage_size', function (): int {
                    return $this->images()->sum('file_size');
                });
            }
        };
        $gallery->name = Str::random();
        $gallery->save();

        $this->assertSame(0, $gallery->storage_size);

        $this->assertTrue(Cache::has('model_attribute_cache.galleries.1.storage_size'));

        $gallery->delete();

        $this->assertFalse(Cache::has('model_attribute_cache.galleries.1.storage_size'));
    }
}
