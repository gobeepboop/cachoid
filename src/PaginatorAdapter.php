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
     * Create a new PaginatorAdapter instance.
     *
     * @param CacheContract $cache
     * @param string|null   $name
     * @param int|null      $perPage
     * @param int|null      $page
     */
    public function __construct(CacheContract $cache, $name = null, ?int $perPage = null, ?int $page = null)
    {
        parent::__construct($cache);

        if ($name) {
            $this->withName($name);
        }

        if (is_int($perPage)) {
            $this->showing($perPage);
        }

        if (is_int($page)) {
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
}
