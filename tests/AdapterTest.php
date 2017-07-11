<?php

namespace Beep\Cachoid\Tests;

use PHPUnit\Framework\TestCase;
use Beep\Cachoid\EloquentAdapter;
use Illuminate\Cache\Repository;
use Illuminate\Cache\ArrayStore;

class AdapterTest extends TestCase
{
    /**
     * @var EloquentAdapter
     */
    protected $adapter;

    public function setUp()
    {
        parent::setUp();

        User::unguard();

        $this->adapter = new EloquentAdapter(new Repository(new ArrayStore));
    }

    /**
     * Tests that models can be retrieved.
     *
     * @return void
     */
    public function test_cached_models_can_be_retrieved(): void
    {
        $expected = new User(['id' => 1, 'name' => 'Robbie']);
        $key      = 'eloquent:users:' . $expected->id;

        $this->adapter->withName(User::class)
                      ->identifiedBy($expected->id)
                      ->remember(10, function () use ($expected): ?User {
                          return $expected;
                      });

        $this->assertTrue($this->adapter->has());
    }
}
