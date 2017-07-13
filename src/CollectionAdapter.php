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
     * {@inheritdoc}
     */
    public function configure(...$attributes): void
    {
        if ($name = data_get($attributes, 0)) {
            $this->withName($name);
        }

        if (($cappedAt = data_get($attributes, 1)) && is_int($cappedAt)) {
            $this->cappedAt($cappedAt);
        }

        if (($offset = data_get($attributes, 2)) && is_int($offset)) {
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
        return $this->buildKey('collection', $this->name, $this->cappedAt, $this->offset);
    }

    /**
     * {@inheritdoc}
     */
    protected function shouldTagModelKeys(): bool
    {
        return func_get_arg(0) instanceof Collection;
    }

    /**
     * {@inheritdoc}
     *
     * @param Collection $value
     */
    protected function bootEagerlyLoaded($value): void
    {
        $this->cappedAt = ! empty($this->cappedAt) && is_int($this->cappedAt) ? $this->cappedAt : $value->count();
    }
}
