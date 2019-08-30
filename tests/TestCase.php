<?php

namespace Astrotomic\CachableAttributes\Tests;

use Illuminate\Support\Str;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use Illuminate\Cache\CacheManager;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use PHPUnit\Framework\MockObject\MockObject;
use Orchestra\Testbench\TestCase as Orchestra;
use Astrotomic\CachableAttributes\Tests\Models\Image;
use Astrotomic\CachableAttributes\Tests\Models\Gallery;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    protected function setUpDatabase(): void
    {
        Schema::dropIfExists('images');
        Schema::dropIfExists('galleries');

        Schema::create('galleries', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('images', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('file_size')->unsigned();
            $table->integer('gallery_id')->unsigned();
            $table->foreign('gallery_id')->references('id')->on('galleries')->onDelete('cascade');
            $table->timestamps();
        });
    }

    protected function gallery(string $name = null): Gallery
    {
        return new Gallery([
            'name' => $name ?? Str::random(),
        ]);
    }

    protected function image(Gallery $gallery, int $fileSize): Image
    {
        return new Image([
            'gallery_id' => $gallery->getKey(),
            'file_size' => $fileSize,
        ]);
    }

    protected function getCache(): CacheManager
    {
        return $this->app->make('cache');
    }

    /**
     * @return MockObject|Repository
     */
    protected function getCacheRepositoryMock(): Repository
    {
        $repository = $this->getMockBuilder(Repository::class)
            ->setConstructorArgs([new ArrayStore()])
            ->onlyMethods([
                'forget',
                'remember',
                'rememberForever',
            ])
            ->getMock();

        $this->getCache()->extend('array', function ($app, array $config) use ($repository): Repository {
            return $repository;
        });

        return $repository;
    }
}
