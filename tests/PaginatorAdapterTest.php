<?php

namespace Beep\Cachoid\Tests;

use Beep\Cachoid\PaginatorAdapter;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\Paginator;

class PaginatorAdapterTest extends TestCase
{
    /**
     * @var PaginatorAdapter
     */
    protected $adapter;

    public function setUp(): void
    {
        parent::setUp();

        User::unguard();

        $this->adapter = new PaginatorAdapter(new Repository(new ArrayStore));
    }

    /**
     * Tests paginators can be retrieved.
     *
     * @return void
     */
    public function test_cached_paginators_can_be_retrieved(): void
    {
        $page    = 1;
        $perPage = 15;

        $expected = new Paginator(new Collection([
            new User(['id' => 1, 'name' => 'Robbie']),
            new User(['id' => 2, 'name' => 'Michael']),
        ]), $perPage, $page);

        $key = "paginator:users:$perPage:$page";

        $this->adapter->withName(User::class)
                      ->onPage($page)
                      ->showing($perPage)
                      ->remember(10, function () use ($expected): Paginator {
                          return $expected;
                      });

        $this->assertTrue($this->adapter->has($key));

        $actual = $this->adapter->get($key);
        $this->assertInstanceOf(Paginator::class, $actual);
        $this->assertEquals(2, $actual->count());
    }

    /**
     * Tests cached paginators with models are busted on model observer event.
     *
     * @return void
     */
    public function test_cached_paginators_with_models_are_busted_on_model_update(): void
    {
        $page    = 1;
        $perPage = 15;

        $model = tap(new User(['id' => 1, 'name' => 'Robbie']))->save();

        $expected = new Paginator(new Collection([
            $model,
            new User(['id' => 2, 'name' => 'Michael']),
        ]), $perPage, $page);

        $this->manager->paginator()->withName(User::class)
                      ->onPage($page)
                      ->showing($perPage)
                      ->remember(10, function () use ($expected): Paginator {
                          return $expected;
                      });

        $this->assertTrue($this->manager->paginator()->has());

        $model->delete();

        $this->assertFalse($this->manager->paginator()->has());
    }

    /**
     * Test cached paginators will infer metadata for the key.
     *
     * @return void
     */
    public function test_cached_freshly_stored_paginator_without_page_metadata(): void
    {
        $page    = 1;
        $perPage = 15;

        $expected = new Paginator(new Collection([
            new User(['id' => 1, 'name' => 'Robbie']),
            new User(['id' => 2, 'name' => 'Michael']),
        ]), $perPage, $page);

        $this->manager->paginator()->withName(User::class)
                      ->remember(10, function () use ($expected): Paginator {
                          return $expected;
                      });

        $this->assertTrue($this->manager->paginator()->has("paginator:users:$perPage:$page"));
    }
}
