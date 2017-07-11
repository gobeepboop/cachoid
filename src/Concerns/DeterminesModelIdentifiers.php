<?php

namespace Beep\Cachoid\Concerns;

use Beep\Cachoid\Cacheable;
use Illuminate\Support\Collection;

trait DeterminesModelIdentifiers
{
    /**
     * Determines identifiers of a collection or model.
     *
     * @param Collection|Cacheable $value
     *
     * @return null
     */
    protected function determineIds($value)
    {
        if ($value instanceof Collection) {
            $value = $value->reject(function ($item): bool {
                return ! $this->usesCacheable($item);
            });

            if ($value->isEmpty()) {
                return null;
            }

            // Set to Higher Order Messages with map.
            $value = $value->map;
        } elseif (! $this->usesCacheable($value)) {
            return null;
        }

        return $value->cacheableAs();
    }

    /**
     * Determines whether an item is using the Cacheable trait.
     *
     * @param mixed $item
     *
     * @return bool
     */
    protected function usesCacheable($item): bool
    {
        return in_array(Cacheable::class, class_uses_recursive($item));
    }
}
