<?php

namespace Beep\Cachoid\Tests;

use PHPUnit\Framework\TestCase;
use Beep\Cachoid\PaginatorAdapter;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\Paginator;

class PaginatorAdapterTest extends TestCase
{
    /**
     * @var PaginatorAdapter
     */
    protected $adapter;

    public function setUp()
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
}
