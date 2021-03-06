<?php

namespace Beep\Cachoid;

use Illuminate\Support\Manager;

/**
 * Class CachoidManager
 *
 * @method EloquentAdapter      eloquent($name = null, $identifier = null)
 * @method CollectionAdapter    collection($name = null, $name = null, ?int $cappedAt = null, ?int $offset = null)
 * @method PaginatorAdapter     paginator($name = null, ?int $perPage = null, ?int $page = null)
 */
class CachoidManager extends Manager
{
    /**
     * Sets the default keys.
     *
     * @var array
     */
    protected $defaultKeys;

    /**
     * The parameters to append to the built-in adapters.
     *
     * @var array
     */
    protected $appendableParameters = [];

    /**
     * Creates a new Eloquent driver.
     *
     * @return EloquentAdapter
     */
    public function createEloquentDriver(): EloquentAdapter
    {
        return new EloquentAdapter($this->getCacheStore(), ...$this->appendableParameters);
    }

    /**
     * Creates a new Collection driver.
     *
     * @return CollectionAdapter
     */
    public function createCollectionDriver(): CollectionAdapter
    {
        return new CollectionAdapter($this->getCacheStore(), ...$this->appendableParameters);
    }

    /**
     * Create a new Paginator driver.
     *
     * @return PaginatorAdapter
     */
    public function createPaginatorDriver(): PaginatorAdapter
    {
        return new PaginatorAdapter($this->getCacheStore(), ...$this->appendableParameters);
    }

    /**
     * Sets default keys.
     *
     * @param array $keys
     */
    public function setDefaultKeys(...$keys): void
    {
        $this->defaultKeys = is_array($keys[0]) ? $keys[0] : $keys;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultDriver()
    {
        return 'eloquent';
    }

    /**
     * Dynamically handle engine resolution.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (! empty($parameters)) {
            $this->appendableParameters = $parameters;
        }

        $driver = parent::driver($method);

        if (! empty($this->defaultKeys)) {
            $driver->setDefaultKeys($this->defaultKeys);
        }

        if (! empty($this->appendableParameters)) {
            $driver->configure(...$this->appendableParameters);
            $this->appendableParameters = [];
        }

        return $driver;
    }

    /**
     * @param mixed $driver
     *
     * @return void
     * @deprecated Not available.
     */
    public function driver($driver = null)
    {
        //
    }

    /**
     * Get the Cache store.
     *
     * @return mixed
     */
    protected function getCacheStore()
    {
        return $this->app['cache']->store();
    }
}
