<?php

namespace Astrotomic\CachableAttributes;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Cache\Factory as CacheFactoryContract;
use InvalidArgumentException;

/**
 * @property string|null $attributeCachePrefix
 * @property string|null $attributeCacheStore
 * @property string[]|null $cachableAttributes
 *
 * @mixin Model
 */
trait HasCachableAttributes
{
    /** @var array<string, mixed> */
    protected $attributeCache = [];

    public static function bootHasCachableAttributes(): void
    {
        static::deleting(function (Model $model): void {
            $model->flush();
        });
    }

    public function remember(string $attribute, ?int $ttl, Closure $callback)
    {
        if ($ttl === 0 || ! $this->exists) {
            if (! isset($this->attributeCache[$attribute])) {
                $this->attributeCache[$attribute] = value($callback);
            }

            return $this->attributeCache[$attribute];
        }

        if ($ttl === null) {
            return $this->getCacheRepository()->rememberForever($this->getCacheKey($attribute), $callback);
        }

        if($ttl < 0) {
            throw new InvalidArgumentException("The TTL has to be null, 0 or any positive number - you provided `{$ttl}`.");
        }

        return $this->getCacheRepository()->remember($this->getCacheKey($attribute), $ttl, $callback);
    }

    public function rememberForever(string $attribute, Closure $callback)
    {
        return $this->remember($attribute, null, $callback);
    }

    public function forget(string $attribute): bool
    {
        unset($this->attributeCache[$attribute]);

        if (! $this->exists) {
            return true;
        }

        return $this->getCacheRepository()->forget($this->getCacheKey($attribute));
    }

    public function flush(): bool
    {
        $result = true;

        foreach ($this->cachableAttributes ?? [] as $attribute) {
            $result = $this->forget($attribute) ? $result : false;
        }

        return $result;
    }

    protected function getCacheKey(string $attribute): string
    {
        return implode('.', [
            $this->attributeCachePrefix ?? 'model_attribute_cache',
            $this->getConnectionName() ?? 'connection',
            $this->getTable(),
            $this->getKey(),
            $attribute,
        ]);
    }

    protected function getCacheRepository(): CacheRepository
    {
        return $this->getCacheFactory()->store($this->attributeCacheStore);
    }

    protected function getCacheFactory(): CacheFactoryContract
    {
        return app('cache');
    }
}
