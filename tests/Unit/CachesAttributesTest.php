<?php

namespace Astrotomic\CachableAttributes\Tests\Unit;

use InvalidArgumentException;
use Astrotomic\CachableAttributes\Tests\TestCase;
use Astrotomic\CachableAttributes\Tests\Models\Gallery;

final class CachesAttributesTest extends TestCase
{
    /** @test */
    public function it_returns_cached_value_in_second_run(): void
    {
        $gallery = $this->gallery();
        $this->assertSame(0, $gallery->remember('test', 5, function () {
            return 0;
        }));
        $this->assertSame(0, $gallery->remember('test', 5, function () {
            return 5;
        }));
    }

    /** @test */
    public function it_returns_cached_value_for_new_instance_with_same_id(): void
    {
        $gallery1 = $this->gallery();
        $gallery1->save();
        $this->assertSame(0, $gallery1->remember('test', 5, function () {
            return 0;
        }));

        $gallery2 = Gallery::find($gallery1->getKey());
        $this->assertSame(0, $gallery2->remember('test', 5, function () {
            return 5;
        }));
    }

    /** @test */
    public function it_returns_correct_value_for_new_instance_with_different_id(): void
    {
        $gallery1 = $this->gallery();
        $gallery1->save();
        $this->assertSame(0, $gallery1->remember('test', 5, function () {
            return 0;
        }));

        $gallery2 = $this->gallery();
        $gallery2->save();
        $this->assertSame(5, $gallery2->remember('test', 5, function () {
            return 5;
        }));
    }

    /** @test */
    public function it_returns_correct_value_after_forget_call(): void
    {
        $gallery = $this->gallery();
        $gallery->save();
        $this->assertSame(0, $gallery->remember('test', 5, function () {
            return 0;
        }));

        $gallery->forget('test');
        $this->assertSame(5, $gallery->remember('test', 5, function () {
            return 5;
        }));
    }

    /** @test */
    public function it_returns_correct_value_after_flush_call(): void
    {
        $gallery = $this->gallery();
        $gallery->save();
        $this->assertSame(0, $gallery->remember('test', 5, function () {
            return 0;
        }));

        $gallery->flush();
        $this->assertSame(5, $gallery->remember('test', 5, function () {
            return 5;
        }));
    }

    /** @test */
    public function it_returns_correct_value_after_ttl_timeout(): void
    {
        $gallery = $this->gallery();
        $gallery->save();
        $this->assertSame(0, $gallery->remember('test', 1, function () {
            return 0;
        }));

        $this->assertSame(0, $gallery->remember('test', 1, function () {
            return 5;
        }));

        sleep(2);
        $this->assertSame(5, $gallery->remember('test', 1, function () {
            return 5;
        }));
    }

    /** @test */
    public function it_throws_an_exception_if_ttl_is_below_zero(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The TTL has to be null, 0 or any positive number - you provided `-5`.');

        $gallery = $this->gallery();
        $gallery->save();
        $gallery->remember('test', -5, function () {
            return 0;
        });
    }
}
