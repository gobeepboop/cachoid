<?php

namespace Beep\Cachoid\Concerns;

use Closure;
use ReflectionMethod;
use ReflectionException;
use ReflectionParameter;
use Illuminate\Cache\TaggedCache as Cache;

/**
 * Trait DynamicallyResolveCache
 *
 * @package Beep\Cachoid\Concerns
 * @property Cache $cache
 */
trait DynamicallyResolveCache
{
    /**
     * Dynamically handles method calls.
     *
     * @param   string     $method
     * @param   array|null $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $closure = function (?array $params = null) use ($method, $parameters) {
            return $this->cache->$method(...($params ?? $parameters));
        };

        if (! is_string($method)) {
            return null;
        }

        // Determine whether the key should be handled
        // otherwise invoke the closure.
        if (! $this->shouldHandleKey($method)) {
            return value($closure);
        }

        // Search for the index of the key within parameters.
        $index = $this->getIndexOfKey($method);

        if ($index === null) {
            return value($closure);
        }

        // Rewrite the parameters with the key if not
        // called with key.
        if (array_get($parameters, $index) === null) {
            $parameters[$index] = $this->key();
        }

        return $closure($parameters);
    }

    /**
     * Determines if the key should be handled for given method.
     *
     * @param string $method
     *
     * @return bool
     */
    private function shouldHandleKey(string $method): bool
    {
        $reflection = $this->getReflectionOfMethod($method);

        if ($reflection === null) {
            return false;
        }

        return collect($reflection->getParameters())->contains(function (ReflectionParameter $parameter): bool {
            return $parameter->getName() === 'key';
        });
    }

    /**
     * Get the index of the key by given method.
     *
     * @param string $method
     *
     * @return int|null
     */
    private function getIndexOfKey(string $method): ?int
    {
        $index = collect(
            $this->getReflectionOfMethod($method)->getParameters()
        )->search(function (ReflectionParameter $parameter): bool {
            return $parameter->getName() === 'key';
        });

        if ($index === false) {
            return null;
        }

        return (int) $index;
    }

    /**
     * Get a reflection of the method.
     *
     * @param string $method
     *
     * @return ReflectionMethod
     */
    private function getReflectionOfMethod(string $method): ReflectionMethod
    {
        try {
            return new ReflectionMethod($this->cache, $method);
        } catch (ReflectionException $exception) {
            return null;
        }
    }
}
