<?php

namespace Beep\Cachoid;

use Beep\Cachoid\Concerns\DeterminesModelIdentifiers;
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
    use DynamicallyResolveCache, DeterminesModelIdentifiers;

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
     * @param string        $name
     */
    public function __construct(CacheContract $cache, $name = null)
    {
        $this->cache = $cache;
        $this->tags  = new Collection;

        if (! empty($name)) {
            $this->withName($name);
        }
    }

    /**
     * @inheritdoc
     */
    abstract public function configure(...$attributes): void;

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
     * Allows for eagerly loaded callback values to override tags.
     *
     * @param mixed $value
     *
     * @return void
     */
    abstract protected function bootEagerlyLoaded($value): void;

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
        // Determine if the cache doesn't have the item.
        if (! $this->cache->has($this->key())) {
            // Resolve the value, and tag internally.
            $value = $this->eagerlyInvokeAndTag($callback);

            $callback = function () use ($value) {
                return $value;
            };
        }

        // If tags are present, we will rebind the cache driver and reset the tags.
        if ($this->tags->isNotEmpty()) {
            $this->cache = $this->cache->tags($this->tags->toArray());
            $this->tags  = new Collection;
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
        $value     = value($closure);
        $paginator = null;

        if (! $this->shouldTagModelKeys($value)) {
            return $value;
        }

        // Boot the eagerly loaded value.
        $this->bootEagerlyLoaded($value);

        // Pull the Collection from the Paginator.
        if ($value instanceof Paginator) {
            $paginator = $value;
            $value     = $paginator->getCollection();
        }

        // Tap for the first value to determine the Model.
        if (! $this->usesCacheable($model = $value->first())) {
            return $paginator ?? $value;
        }

        // Collect all of the model keys.
        // Afterwards merge with the tags after transformation.
        collect($this->determineIds($value))->transform(function ($key) use ($model) {
            return $this->generateUniqueModelTag($model, $key);
        })->each(function ($tag): void {
            $this->tags->push($tag);
        });

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
        return is_numeric($key) === true ? "{$this->transformClassName($model)}-$key" : $key;
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
     *
     * @see Adapter::key()
     */
    protected function buildKey(?string $name, ... $namespacedBy): string
    {
        $seperator = ':';
        $key       = new Collection;

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
