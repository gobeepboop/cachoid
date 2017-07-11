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
        return new EloquentAdapter($this->app['cache'], ...$this->appendableParameters);
    }

    /**
     * Creates a new Collection driver.
     *
     * @return CollectionAdapter
     */
    public function createCollectionDriver(): CollectionAdapter
    {
        return new CollectionAdapter($this->app['cache'], ...$this->appendableParameters);
    }

    /**
     * Create a new Paginator driver.
     *
     * @return PaginatorAdapter
     */
    public function createPaginatorDriver(): PaginatorAdapter
    {
        return new PaginatorAdapter($this->app['cache'], ...$this->appendableParameters);
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

        return parent::driver($method);
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
}
