<?php

namespace Beep\Cachoid;

use Beep\Cachoid\Contracts\Adapter as Contract;
use Illuminate\Contracts\Cache\Repository as CacheContract;
use Illuminate\Database\Eloquent\Collection;

class CollectionAdapter extends Adapter implements Contract
{
    /**
     * The amount of records capped at.
     *
     * @var int
     */
    protected $cappedAt = 0;

    /**
     * Indicates the offset.
     *
     * @var int
     */
    protected $offset = 0;

    /**
     * Create a new CollectionAdapter instance.
     *
     * @param CacheContract $cache
     * @param string|null   $name
     * @param int|null      $cappedAt
     * @param int|null      $offset
     */
    public function __construct(CacheContract $cache, $name = null, ?int $cappedAt = null, ?int $offset = null)
    {
        parent::__construct($cache);

        if (! is_null($name)) {
            $this->withName($name);
        }

        if (is_int($cappedAt)) {
            $this->cappedAt($cappedAt);
        }

        if (is_int($offset)) {
            $this->withOffset($offset);
        }
    }

    /**
     * Sets the amount of records capped at.
     *
     * @param int $cappedAt
     *
     * @return CollectionAdapter
     */
    public function cappedAt(int $cappedAt): CollectionAdapter
    {
        $this->cappedAt = $cappedAt;

        return $this;
    }

    /**
     * Sets the offset.
     *
     * @param int $offset
     *
     * @return CollectionAdapter
     */
    public function withOffset(int $offset): CollectionAdapter
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function key(): string
    {
        return "collection:{$this->name}.{$this->cappedAt}.{$this->offset}";
    }

    /**
     * {@inheritdoc}
     */
    protected function shouldTagModelKeys(): bool
    {
        return func_get_arg(0) instanceof Collection;
    }
}
