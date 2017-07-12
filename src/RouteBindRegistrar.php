<?php

namespace Beep\Cachoid;

use Closure;
use Illuminate\Routing\Router;
use Illuminate\Support\Str as s;
use Illuminate\Database\Eloquent\Model;

class RouteBindRegistrar
{
    /**
     * @var Router
     */
    protected $router;

    /**
     * @var CachoidManager
     */
    protected $cachoid;

    /**
     * Create a new RouteBindRegistrar.
     *
     * @param Router         $router
     * @param CachoidManager $cachoid
     */
    public function __construct(Router $router, CachoidManager $cachoid)
    {
        $this->router  = $router;
        $this->cachoid = $cachoid;
    }

    /**
     * Registers a cachable model explicit route binding.
     *
     * @param Model|Cacheable $model
     * @param Closure|null    $closure
     */
    public function model(Model $model, ?Closure $closure = null): void
    {
        $this->router->bind(
            $this->determineBindingKey($model),
            $this->getBinder($model, $closure)
        );
    }

    /**
     * Get the binder closure.
     *
     * @param Model        $model
     * @param Closure|null $closure
     *
     * @return Closure
     */
    public function getBinder(Model $model, ?Closure $closure = null): Closure
    {
        return function ($value) use ($model, $closure) {
            return $this->cachoid->eloquent($model, $value)->remember(
                $model->cacheableFor(),
                $closure ?: function () use ($model, $value) {
                    return $model->where($model->getRouteKeyName(), $value)->first();
                }
            );
        };
    }

    /**
     * Determines the binding key for a given model.
     *
     * @param Model $model
     * @param bool  $plural
     *
     * @return string
     */
    protected function determineBindingKey(Model $model, bool $plural = false): string
    {
        $method = $plural === false ? 'singular' : 'plural';

        return s::{$method}(s::snake(s::lower(class_basename($model))));
    }
}
