<?php

namespace Beep\Cachoid\Contracts;

use Illuminate\Contracts\Cache\Repository as CacheContract;

interface Adapter
{
    /**
     * {@inheritdoc}
     */
    public function driver(): CacheContract;

    /**
     * Sets the default keys.
     *
     * @param \string[]|[] ...$keys
     *
     * @return Adapter
     */
    public function setDefaultKeys(...$keys);

    /**
     * Tags the data.
     *
     * @param array $tags
     *
     * @return Adapter
     */
    public function tags(array $tags): Adapter;
}
