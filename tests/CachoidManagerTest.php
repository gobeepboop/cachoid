<?php

namespace Beep\Cachoid\Tests;

use Beep\Cachoid\CachoidManager;
use Beep\Cachoid\EloquentAdapter;
use Illuminate\Cache\Repository as Cache;
use Illuminate\Contracts\Cache\Repository as CacheContract;
use Illuminate\Contracts\Cache\Store as StoreContract;
use Illuminate\Cache\ArrayStore;
use Illuminate\Container\Container;
use PHPUnit\Framework\TestCase;

class CachoidManagerTest extends TestCase
{
    /**
     * @var CachoidManager
     */
    protected $manager;

    /**
     * The setup method of the Test Case.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $app = new Container();
        $app->bind(StoreContract::class, ArrayStore::class);
        $app->bind(CacheContract::class, Cache::class);
        $app->alias(CacheContract::class, 'cache');

        $this->manager = new CachoidManager($app);
    }

    /**
     * Tests dynamic __call's to the EloquentAdapter.
     *
     * @return void
     */
    public function test_dynamic_calls_to_eloquent_with_name_and_identifier(): void
    {
        $eloquent = $this->manager->eloquent('tests', 'bar');

        $this->assertInstanceOf(EloquentAdapter::class, $eloquent);

        $eloquent->remember(10, function (): string {
            return 'test';
        });

        $this->assertTrue($eloquent->has('models:tests:bar'));
    }
}
