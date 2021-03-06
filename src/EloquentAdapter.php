<?php

namespace Beep\Cachoid;

use Beep\Cachoid\Contracts\Adapter as AdapterContract;
use Illuminate\Contracts\Cache\Repository as CacheContract;
use Illuminate\Database\Eloquent\Model;

/**
 * Class EloquentAdapter
 *
 * @package Beep\Cachoid
 */
class EloquentAdapter extends Adapter implements AdapterContract
{
    /**
     * @var string
     */
    protected $identifier;

    /**
     * {@inheritdoc}
     */
    public function configure(...$attributes): void
    {
        if ($name = data_get($attributes, 0)) {
            $this->withName($name);
        }

        if ($identifier = data_get($attributes, 1)) {
            $this->identifiedBy($identifier);
        }
    }

    /**
     * Sets the identifier by given id.
     *
     * @param string|int $identifier
     *
     * @return EloquentAdapter
     */
    public function identifiedBy($identifier): EloquentAdapter
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * Destroys a model.
     *
     * @param Model|Cacheable $model
     *
     * @return bool
     */
    public function destroy(Model $model)
    {
        // Set the key.
        $this->withName($model)->identifiedBy($model->cacheableAs());

        // Flush all the potentially collected and paginated tags of the model.
        $this->cache->tags([$this->generateUniqueModelTag($model, $model->cacheableAs())])->flush();

        // Destroy the model itself from the cache.
        return $this->cache->forget($this->key());
    }

    /**
     * {@inheritdoc}
     */
    protected function key(): string
    {
        return $this->buildKey('eloquent', $this->name, $this->identifier);
    }

    /**
     * {@inheritdoc}
     */
    protected function shouldTagModelKeys(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function bootEagerlyLoaded($value): void
    {
        //
    }
}
