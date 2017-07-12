<?php

namespace Beep\Cachoid;

use Illuminate\Support\Facades\Facade as Base;

class Cachoid extends Base
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor(): CachoidManager
    {
        return static::$app->make(CachoidManager::class);
    }
}
