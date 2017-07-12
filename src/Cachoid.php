<?php

namespace Beep\Cachoid;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Model;

class Cachoid
{
    /**
     * @var Application
     */
    protected static $application;

    /**
     * Registers implicit route bindings for models.
     *
     * @param Model[] $models
     *
     * @return void
     */
    public static function routeable(Model ...$models): void
    {
        collect($models)->each(function (Model $model): void {
            static::$application->make(RouteBindRegistrar::class)->model($model);
        });
    }

    /**
     * @return Application
     */
    public static function getApplication(): Application
    {
        return self::$application ?? (static::$application = app());
    }

    /**
     * @param Application $application
     */
    public static function setApplication(Application $application)
    {
        self::$application = $application;
    }
}
