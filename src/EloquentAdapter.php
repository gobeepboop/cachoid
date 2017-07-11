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
     * Create a new EloquentAdapter instance.
     *
     * @param CacheContract $cache
     * @param string|null   $name
     * @param string|null   $identifier
     */
    public function __construct(CacheContract $cache, $name = null, $identifier = null)
    {
        parent::__construct($cache);

        if (! is_null($name) && ! is_null($identifier)) {
            $this->withName($name)->identifiedBy($identifier);
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
     * @param Model $model
     *
     * @return bool
     */
    public function destroy(Model $model)
    {
        // Set the key.
        $this->withName($model)->identifiedBy($model->getKey());

        // Flush all the potentially paginated tags of the model.
        $this->cache->tags([$this->generateUniqueModelTag($model, $model->getKey())])->flush();

        // Forget the actual model by key.
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
}
