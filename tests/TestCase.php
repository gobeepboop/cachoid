<?php

namespace Beep\Cachoid\Tests;

use Beep\Cachoid\CachoidManager;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Events\Dispatcher;
use PHPUnit\Framework\TestCase as Base;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository as Cache;
use Illuminate\Contracts\Cache\Repository as CacheContract;
use Illuminate\Contracts\Cache\Store as StoreContract;

class TestCase extends Base
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

        $app = new Container;

        // Set the store as a singleton to assist with
        // cross driver usage.
        $app->singleton(StoreContract::class, ArrayStore::class);

        $app->bind(CacheContract::class, Cache::class);

        // Set a dispatcher when resolving a cache contract.
        $app->resolving(CacheContract::class, function ($cache, $app): void {
            $cache->setEventDispatcher(new Dispatcher);
        });

        $app->alias(CacheContract::class, 'cache');

        $db = new DB($app);

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

        $this->app = $app;

        $this->manager = new CachoidManager($this->app);

        User::setCachoidManager($this->manager);
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
}
