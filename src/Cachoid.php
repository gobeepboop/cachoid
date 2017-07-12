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
     * @param Model[]|string[] $models
     *
     * @return void
     */
    public static function routeable(...$models): void
    {
        collect($models)->transform(function ($model) {
            return class_exists($model) ? new $model : $model;
        })->each(function (Model $model): void {
            static::getApplication()->make(RouteBindRegistrar::class)->model($model);
        });
    }

    /**
     * @return Application
     */
    public static function getApplication(): Application
    {
        return static::$application ?? app();
    }

    /**
     * @param Application $application
     */
    public static function setApplication(Application $application)
    {
        static::$application = $application;
    }
}
