<?php

namespace Beep\Cachoid;

use Closure;
use Beep\Cachoid\Contracts\Adapter as Contract;
use Illuminate\Contracts\Cache\Repository as CacheContract;
use Illuminate\Cache\TaggedCache as Cache;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str as s;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

/**
 * Class Adapter
 *
 * @mixin Cache
 */
abstract class Adapter implements Contract
{
    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @var Collection
     */
    protected $tags;

    /**
     * @var string
     */
    protected $name;

    /**
     * Create a new Adapter instance.
     *
     * @param CacheContract $cache
     */
    public function __construct(CacheContract $cache)
    {
        $this->cache = $cache;
        $this->tags  = new Collection;
    }

    /**
     * {@inheritdoc}
     */
    public function driver(): CacheContract
    {
        return $this->cache;
    }

    /**
     * Sets the key for a given value.
     *
     * @param mixed $value
     *
     * @return $this|Adapter
     */
    public function withName($value): Adapter
    {
        $this->name = is_object($value) || class_exists($value)
            ? $this->transformClassName($value) : $value;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function tags(array $tags): Contract
    {
        $this->tags->merge($tags)->flatten();

        return $this;
    }

    /**
     * Get an item from the cache, or store the default value.
     *
     * @param  \DateTime|float|int $minutes
     * @param  \Closure            $callback
     *
     * @return mixed
     */
    public function remember($minutes, Closure $callback)
    {
        $callback = $this->processTaggables($callback);

        return $this->cache->remember($this->key(), $minutes, $callback);
    }

    /**
     * Get an item from the cache, or store the default value forever.
     *
     * @param Closure $callback
     *
     * @return mixed
     */
    public function rememberForever(Closure $callback)
    {
        $callback = $this->processTaggables($callback);

        return $this->cache->rememberForever($this->key(), $callback);
    }

    /**
     * Dynamically handles method calls.
     *
     * @param   string     $method
     * @param   array|null $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->cache->$method(...$parameters);
    }

    /**
     * Determines whether model keys should be tagged.
     *
     * @return bool
     */
    abstract protected function shouldTagModelKeys(): bool;

    /**
     * Determines the stored key.
     *
     * @return string
     */
    abstract protected function key(): string;

    /**
     * Processes the "taggables" for the transaction.
     *
     * @param Closure $callback
     *
     * @return Closure
     */
    protected function processTaggables(Closure $callback): Closure
    {
        if (! $this->cache->has($this->key())) {
            $callback = function () use ($callback) {
                return $this->eagerlyInvokeAndTag($callback);
            };
        }

        if (! $this->tags->isEmpty()) {
            $this->cache->tags($this->tags->toArray());
        }

        return $callback;
    }

    /**
     * Eagerly resolves the closure and tags the keys.
     *
     * @param Closure $closure
     *
     * @return EloquentCollection|mixed
     */
    protected function eagerlyInvokeAndTag(Closure $closure)
    {
        /** @var EloquentCollection|mixed $value */
        $value      = value($closure);
        $paginator  = null;

        if (! $this->shouldTagModelKeys($value)) {
            return $value;
        }

        // Pull the Collection from the Paginator.
        if ($value instanceof Paginator) {
            $paginator = $value;
            $value     = $paginator->getCollection();
        }

        // Tap for the first value to determine the Model.
        $model = $value->first();

        // Collect all of the model keys.
        // Afterwards merge with the tags after transformation.
        $this->tags->merge(collect($value->modelKeys())->transform(function ($key) use ($model) {
            return $this->boostEntropyForModelTag($model, $key);
        }));

        return $paginator ?? $value;
    }

    /**
     * Boosts entropy on a tagged model and key.
     *
     * @param mixed      $model
     * @param string|int $key
     *
     * @return string
     */
    protected function boostEntropyForModelTag($model, $key): string
    {
        return is_numeric($key) === true ? $this->transformClassName($model) . ".$key" : $key;
    }

    /**
     * Transforms a class name.
     *
     * @param string $class
     *
     * @return string
     */
    protected function transformClassName($class): string
    {
        return is_object($class) || class_exists($class) === true
            ? s::lower(s::snake(s::plural(class_basename($class)))) : $class;
    }
}
