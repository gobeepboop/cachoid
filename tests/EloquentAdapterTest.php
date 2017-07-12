<?php

namespace Beep\Cachoid\Tests;

use Beep\Cachoid\EloquentAdapter;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;

class EloquentAdapterTest extends TestCase
{
    /**
     * @var EloquentAdapter
     */
    protected $adapter;

    public function setUp(): void
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

        $this->manager->setDefaultKeys($randomId);

        $this->manager->eloquent()->withName(User::class)
                      ->identifiedBy($expected->id)
                      ->remember(10, function () use ($expected): ?User {
                          return $expected;
                      });

        $this->assertTrue($this->manager->eloquent()->has());
    }

    /**
     * Tests cached models can be found with findInCache.
     */
    public function test_cached_models_can_be_found(): void
    {
        /** @var User $expected */
        $expected = tap(new User(['name' => 'Robbie']))->save();

        $this->assertInstanceOf(User::class, User::findInCache($expected->id));

        // Flush the event listeners to ensure the above logic
        // pulled from the cache itself as the delete shouldn't
        // trigger the bustable() method.
        User::flushEventListeners();
        $expected->delete();

        $this->assertInstanceOf(User::class, $this->manager->eloquent(User::class, $expected->id)->get());
    }
}
