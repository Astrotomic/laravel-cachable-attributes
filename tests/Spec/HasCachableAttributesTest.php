<?php

namespace Astrotomic\CachableAttributes\Tests\Spec;

use Astrotomic\CachableAttributes\Tests\TestCase;

final class HasCachableAttributesTest extends TestCase
{
    /** @test */
    public function it_does_not_call_cache_repository_methods_if_model_does_not_exist(): void
    {
        $repository = $this->getCacheRepositoryMock();
        $repository->expects($this->never())->method('forget');
        $repository->expects($this->never())->method('remember');
        $repository->expects($this->never())->method('rememberForever');

        $gallery = $this->gallery();

        $gallery->remember('test', null, function () {
            return 0;
        });
        $gallery->remember('test', 0, function () {
            return 0;
        });
        $gallery->remember('test', 1, function () {
            return 0;
        });
        $gallery->forget('test');
    }

    /** @test */
    public function it_calls_cache_repository_forget_method(): void
    {
        $repository = $this->getCacheRepositoryMock();
        $repository->expects($this->once())->method('forget')->with('model_attribute_cache.testing.galleries.1.test')->willReturn(true);
        $repository->expects($this->never())->method('remember');
        $repository->expects($this->never())->method('rememberForever');

        $gallery = $this->gallery();
        $gallery->save();

        $gallery->forget('test');
    }

    /** @test */
    public function it_calls_cache_repository_remember_method(): void
    {
        $callback = function () {
            return 0;
        };

        $repository = $this->getCacheRepositoryMock();
        $repository->expects($this->never())->method('forget');
        $repository->expects($this->once())->method('remember')->with('model_attribute_cache.testing.galleries.1.test', 5, $callback);
        $repository->expects($this->never())->method('rememberForever');

        $gallery = $this->gallery();
        $gallery->save();

        $gallery->remember('test', 5, $callback);
    }

    /** @test */
    public function it_calls_cache_repository_rememberForever_method(): void
    {
        $callback = function () {
            return 0;
        };

        $repository = $this->getCacheRepositoryMock();
        $repository->expects($this->never())->method('forget');
        $repository->expects($this->never())->method('remember');
        $repository->expects($this->exactly(2))->method('rememberForever')->with('model_attribute_cache.testing.galleries.1.test', $callback);

        $gallery = $this->gallery();
        $gallery->save();

        $gallery->remember('test', null, $callback);
        $gallery->rememberForever('test', $callback);
    }

    /** @test */
    public function it_calls_cache_repository_forget_method_if_flushed(): void
    {
        $repository = $this->getCacheRepositoryMock();
        $repository->expects($this->exactly(2))->method('forget')->withConsecutive(
            ['model_attribute_cache.testing.galleries.1.test'],
            ['model_attribute_cache.testing.galleries.1.storage_size']
        )->willReturn(true);
        $repository->expects($this->never())->method('remember');
        $repository->expects($this->never())->method('rememberForever');

        $gallery = $this->gallery();
        $gallery->save();

        $gallery->flush();
    }
}
