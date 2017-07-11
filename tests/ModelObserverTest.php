<?php

namespace Beep\Cachoid\Tests;

use Beep\Cachoid\ModelObserver;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Events\Dispatcher;
use PHPUnit\Framework\TestCase;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Cache\ArrayStore;
use Beep\Cachoid\CachoidManager;
use Illuminate\Cache\Repository as Cache;
use Illuminate\Contracts\Cache\Repository as CacheContract;
use Illuminate\Contracts\Cache\Store as StoreContract;
use Illuminate\Container\Container;

class ModelObserverTest extends TestCase
{
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
        Eloquent::unguard();

        User::setEventDispatcher(new Dispatcher);
        User::observe(ModelObserver::class);

        $db = new DB;
        $db->addConnection([
            'driver'   => 'sqlite',
            'database' => ':memory:',
        ]);
        $db->bootEloquent();
        $db->setAsGlobal();

        $this->schema()->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });

        $app = new Container();
        $app->bind(StoreContract::class, ArrayStore::class);
        $app->bind(CacheContract::class, Cache::class);
        $app->alias(CacheContract::class, 'cache');

        $this->manager = new CachoidManager($app);
        User::setCachoidManager($this->manager);
    }

    /**
     * Tear down the Test Case.
     *
     * @return void
     */
    public function tearDown(): void
    {
        $this->schema()->drop('users');
    }

    /**
     * Tests models are cached when saved.
     *
     * @return void
     */
    public function test_models_are_cached_when_saved(): void
    {
        $user   = $this->tapAndSaveUser();
        $actual = $this->manager->eloquent()->get("eloquent:users:{$user->id}");

        $this->assertInstanceOf(User::class, $actual);
    }

    /**
     * Tests models are busted when deleted.
     *
     * @return void
     */
    public function test_models_are_busted_when_deleted(): void
    {
        $user = $this->tapAndSaveUser();
        $user->forceDelete();

        $actual = $this->manager->eloquent()->get("users.{$user->id}");

        $this->assertNull($actual);
    }

    /**
     * Creates, taps and saves a User.
     *
     * @return User
     */
    protected function tapAndSaveUser(): User
    {
        return tap(new User(['name' => 'Robbie']))->save();
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
