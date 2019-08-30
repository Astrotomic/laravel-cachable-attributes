<?php

namespace Astrotomic\CachableAttributes;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * @property string $attributeCachePrefix
 * @property string[] $cachableAttributes
 *
 * @mixin Model
 */
trait HasCachableAttributes
{
    /** @var array<string, mixed> */
    protected $attributeCache = [];

    public function remember(string $key, ?int $ttl, Closure $callback)
    {
        if ($ttl === 0 || !$this->exists) {
            if (!isset($this->attributeCache[$key])) {
                $this->attributeCache[$key] = value($callback);
            }

            return $this->attributeCache[$key];
        }

        if ($ttl === null) {
            return Cache::rememberForever($this->getCacheKey($key), $callback);
        }

        return Cache::remember($this->getCacheKey($key), $ttl, $callback);
    }

    public function rememberForever(string $key, Closure $callback)
    {
        return $this->remember($key, null, $callback);
    }

    public function forget(string $key): bool
    {
        unset($this->attributeCache[$key]);

        if(! $this->exists) {
            return true;
        }

        return Cache::forget($this->getCacheKey($key));
    }

    public function flush(): bool
    {
        $result = true;

        foreach($this->cachableAttributes as $attribute) {
            $result = $this->forget($attribute) ? $result : false;
        }

        return $result;
    }

    protected function getCacheKey(string $key): string
    {
        return sprintf('%s.%s.%d.%s',
            $this->attributeCachePrefix ?? 'model_attribute_cache',
            strtolower(class_basename(static::class)),
            $this->getKey(),
            $key
        );
    }
}
