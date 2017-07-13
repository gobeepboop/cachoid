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
     * Configures the driver.
     *
     * @param array ...$attributes
     *
     * @return void
     */
    public function configure(...$attributes): void;

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
