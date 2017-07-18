<?php

namespace Beep\Cachoid\Tests;

use Beep\Cachoid\CachoidManager;
use Illuminate\Contracts\Cache\Factory;
use Illuminate\Cache\CacheManager;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Events\Dispatcher;
use PHPUnit\Framework\TestCase as Base;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Contracts\Cache\Repository as CacheContract;
use Illuminate\Config\Repository as Configuration;

abstract class TestCase extends Base
{
    /**
     * @var Container
     */
    protected $app;

    /**
     * @var CachoidManager
     */
    protected $manager;

    /**
     * Setup the Test Case.
     *
     * @return void
     */
    public function setUp(): void
    {
        Eloquent::clearBootedModels();
        Eloquent::unguard();
        Eloquent::setEventDispatcher(new Dispatcher);

        $this->app = new Container;

        // Set a dispatcher when resolving a cache contract.
        $this->app->resolving(CacheContract::class, function ($cache, $app): void {
            $cache->setEventDispatcher(new Dispatcher);
        });

        $this->app->instance('config', new Configuration([
            'cache' => [
                'stores'    => [
                    'array' => [
                        'driver' => 'array'
                    ]
                ]
            ]
        ]));

        // Setup the Cache Manager and set the default driver.
        $this->app->singleton(CacheManager::class, function (): CacheManager {
            $manager = new CacheManager($this->app);
            $manager->setDefaultDriver('array');
            return $manager;
        });

        // Bind the contract to the concrete, then alias.
        $this->app->singleton(Factory::class, CacheManager::class);
        $this->app->alias(Factory::class, 'cache');

        $db = new DB($this->app);

        $db->addConnection([
            'driver'   => 'sqlite',
            'database' => ':memory:',
        ]);

        $db->bootEloquent();
        $db->setAsGlobal();

        // Setup a users table.
        $this->schema()->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });

        $this->manager = new CachoidManager($this->app);

        User::setCachoidManager($this->manager);
    }

    /**
     * Tear down the test case.
     *
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();

        \Mockery::close();
    }

    /**
     * Gets the Schema Builder.
     *
     * @return mixed
     */
    protected function schema()
    {
        return $this->connection()->getSchemaBuilder();
    }

    /**
     * Resolves the Connection interface.
     *
     * @return \Illuminate\Database\ConnectionInterface
     */
    protected function connection()
    {
        return Eloquent::getConnectionResolver()->connection();
    }

    /**
     * Helper method to hash keys.
     *
     * @param string $value
     *
     * @return string
     */
    protected function hash(string $value): string
    {
        return hash('SHA256', $value);
    }
}
