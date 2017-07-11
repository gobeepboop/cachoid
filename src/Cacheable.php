<?php

namespace Beep\Cachoid;

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
     * Caches the Model.
     *
     * @return void
     */
    public function cacheable(): void
    {
        $this->getCachoidManager()->eloquent(self::class, $this->getKey())->rememberForever(function () {
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
     * Retrieves a Cachoid Manager instance.
     *
     * @return CachoidManager
     */
    private function getCachoidManager(): CachoidManager
    {
        return static::$cachoidManager ?? app(CachoidManager::class);
    }
}
