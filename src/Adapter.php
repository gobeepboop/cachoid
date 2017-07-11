<?php

namespace Beep\Cachoid;

use Beep\Cachoid\Concerns\DynamicallyResolveCache;
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
 * @method bool has(?string $key = null)
 */
abstract class Adapter implements Contract
{
    use DynamicallyResolveCache;

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
     * Indicates default keys to be prefixed to namespace.
     *
     * @var array
     */
    protected $defaultKeys = [];

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
     * {@inheritdoc}
     */
    public function setDefaultKeys(...$keys)
    {
        $this->defaultKeys = is_array($keys[0]) ? $keys[0] : $keys;

        return $this;
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
            return $this->generateUniqueModelTag($model, $key);
        }));

        return $paginator ?? $value;
    }

    /**
     * Generates a unique model tag.
     *
     * @param mixed      $model
     * @param string|int $key
     *
     * @return string
     */
    protected function generateUniqueModelTag($model, $key): string
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

    /**
     * Builds the key.
     *
     * @param null|string $name
     * @param array       ...$namespacedBy
     *
     * @return string
     */
    protected function buildKey(?string $name, ... $namespacedBy): string
    {
        $seperator = ':';
        $key = new Collection;

        if (! is_null($name)) {
            $key->push($name);
        }

        $key->push($this->defaultKeys);

        if (! empty($namespacedBy)) {
            $key->push($namespacedBy);
        }

        return $key->flatten()->filter(function ($item): bool {
            return ! is_null($item);
        })->implode($seperator);
    }
}
