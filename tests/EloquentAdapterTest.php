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

        $this->assertTrue($this->adapter->has($key));
    }

    /**
     * Tests that models can be retrieved with default keys.
     *
     * @return void
     */
    public function test_cached_models_with_default_keys_can_be_retrieved(): void
    {
        $expected = new User(['id' => 1, 'name' => 'Robbie']);
        $randomId = '12345';
        $key      = 'eloquent:' . $randomId . ':users:' . $expected->id;

        $this->adapter->setDefaultKeys($randomId);

        $this->adapter->withName(User::class)
                      ->identifiedBy($expected->id)
                      ->remember(10, function () use ($expected): ?User {
                          return $expected;
                      });

        $this->assertTrue($this->adapter->has($key));
    }
}
