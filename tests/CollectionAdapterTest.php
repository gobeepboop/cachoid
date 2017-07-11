<?php

namespace Beep\Cachoid\Tests;

use Beep\Cachoid\CollectionAdapter;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\TestCase;

class CollectionAdapterTest extends TestCase
{
    /**
     * @var CollectionAdapter
     */
    protected $adapter;

    public function setUp()
    {
        parent::setUp();

        User::unguard();

        $this->adapter = new CollectionAdapter(new Repository(new ArrayStore));
    }

    /**
     * Tests tagged collections can be retrieved.
     *
     * @return void
     */
    public function test_cached_collections_can_be_retrieved(): void
    {
        $expected = new Collection([
            new User(['id' => 1, 'name' => 'Robbie']),
            new User(['id' => 2, 'name' => 'Michael'])
        ]);

        $key      = 'collection:users:15:0';

        $this->adapter->withName(User::class)
                      ->cappedAt(15)
                      ->remember(10, function () use ($expected): Collection {
                          return $expected;
                      });

        $this->assertTrue($this->adapter->has($key));

        $actual = $this->adapter->get($key);
        $this->assertInstanceOf(Collection::class, $actual);
        $this->assertEquals(2, $actual->count());
    }
}
