<?php

namespace Beep\Cachoid;

use Beep\Cachoid\Contracts\Adapter as Contract;
use Illuminate\Contracts\Cache\Repository as CacheContract;
use Illuminate\Contracts\Pagination\Paginator;

class PaginatorAdapter extends Adapter implements Contract
{
    /**
     * Indicates the current page.
     *
     * @var int
     */
    protected $page = 0;

    /**
     * Indicates the amount of records per page.
     *
     * @var int
     */
    protected $perPage = 0;

    /**
     * {@inheritdoc}
     */
    public function configure(...$attributes): void
    {
        if ($name = data_get($attributes, 0)) {
            $this->withName($name);
        }

        if (($perPage = data_get($attributes, 1)) && is_int($perPage)) {
            $this->showing($perPage);
        }

        if (($page = data_get($attributes, 2)) && is_int($page)) {
            $this->onPage($page);
        }
    }

    /**
     * Sets the current page.
     *
     * @param int $page
     *
     * @return PaginatorAdapter
     */
    public function onPage(int $page): PaginatorAdapter
    {
        $this->page = $page;

        return $this;
    }

    /**
     * Sets the amount of records per page.
     *
     * @param int $perPage
     *
     * @return PaginatorAdapter
     */
    public function showing(int $perPage): PaginatorAdapter
    {
        $this->perPage = $perPage;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function shouldTagModelKeys(): bool
    {
        return func_get_arg(0) instanceof Paginator;
    }

    /**
     * {@inheritdoc}
     */
    protected function key(): string
    {
        return $this->buildKey('paginator', $this->name, $this->perPage, $this->page);
    }

    /**
     * {@inheritdoc}
     *
     * @param Paginator $value
     */
    protected function bootEagerlyLoaded($value): void
    {
        if ($value->perPage()) {
            $this->showing($value->perPage());
        }

        if ($value->currentPage()) {
            $this->onPage($value->currentPage());
        }
    }
}
