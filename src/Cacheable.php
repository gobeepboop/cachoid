<?php

namespace Beep\Cachoid;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Trait Cacheable
 *
 * @mixin Model
 */
trait Cacheable
{
    /**
     * @var CachoidManager
     */
    protected static $cachoidManager;

    /**
     * The amount of time, in minutes, to cache the model.
     *
     * @var int
     */
    protected $cacheableFor = 30;

    /**
     * Boot the Cacheable trait and register the model observer.
     *
     * @return void
     */
    public static function bootCacheable(): void
    {
        static::observe(ModelObserver::class);

        (new static)->bootCacheableMacros();
    }

    /**
     * Boot the macros for the Builder.
     *
     * @return void
     */
    public function bootCacheableMacros(): void
    {
        $static = static::class;

        Builder::macro('findInCache', function (string $identifier) use ($static) {
            return (function () use ($identifier) {
                return $this->findInCacheOrWarm($identifier);
            })->bindTo(new $static, $static)();
        });
    }

    /**
     * Caches the Model.
     *
     * @return void
     */
    public function cacheable(): void
    {
        // If the model wasn't previously created, destroy any collected and paginated models.
        if (! $this->wasRecentlyCreated) {
            $this->bustable();
        }

        $this->getCachoidManager()->eloquent(self::class, $this->cacheableAs())->rememberForever(function () {
            return $this;
        });
    }

    /**
     * Destroys the Model.
     *
     * @return void
     */
    public function bustable(): void
    {
        $this->getCachoidManager()->eloquent()->destroy($this);
    }

    /**
     * Get the identifier to cache as.
     *
     * @return mixed
     */
    public function cacheableAs()
    {
        return $this->getKey();
    }

    /**
     * Get the time, in minutes, for caching the model.
     *
     * @return int
     */
    public function cacheableFor(): int
    {
        return $this->cacheableFor;
    }

    /**
     * Set the Cachoid Manager instance.
     *
     * @param CachoidManager $cachoidManager
     *
     * @return $this
     */
    public static function setCachoidManager(CachoidManager $cachoidManager)
    {
        static::$cachoidManager = $cachoidManager;
    }

    /**
     * Finds a model in the cache and stores it.
     *
     * @param string $identifier
     *
     * @return static|null
     */
    protected function findInCacheOrWarm(string $identifier)
    {
        return $this->getCachoidManager()->eloquent(static::class, $identifier)
                                         ->rememberForever(function () use ($identifier) {
                                             return $this->find($identifier);
                                         });
    }

    /**
     * Retrieves a Cachoid Manager instance.
     *
     * @return CachoidManager
     */
    private function getCachoidManager(): CachoidManager
    {
        return static::$cachoidManager ?? app(CachoidManager::class);
    }
}
