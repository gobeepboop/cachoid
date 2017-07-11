<?php

namespace Beep\Cachoid\Tests;

use Beep\Cachoid\EloquentAdapter;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use PHPUnit\Framework\TestCase;

class EloquentAdapterTest extends TestCase
{
    /**
     * @var EloquentAdapter
     */
    protected $adapter;

    public function setUp()
    {
        parent::setUp();

        $this->adapter = new EloquentAdapter(new Repository(new ArrayStore));
    }

    /**
     * Tests that models can be retrieved.
     *
     * @return void
     */
    public function test_cached_models_can_be_retrieved(): void
    {
        $expected = new User(['name' => 'Robbie']);
        $key      = 'models:users:' . $expected->id;

        $this->adapter->withName(User::class)
                      ->identifiedBy($expected->id)
                      ->remember(10, function () use ($expected): ?User {
                          return $expected;
                      });

        $this->assertTrue($this->adapter->has($key));
    }
}
