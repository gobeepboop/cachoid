<?php

namespace Beep\Cachoid\Tests;

use Beep\Cachoid\Facade;
use Beep\Cachoid\CachoidManager;
use Beep\Cachoid\EloquentAdapter;

class FacadeTest extends TestCase
{
    /**
     * Setup the Test Case.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->app->bind(CachoidManager::class, function () {
            return $this->manager;
        });

        Facade::setFacadeApplication($this->app);
    }

    /**
     * Tests the Cachoid facade references the cachoid manager.
     */
    public function test_facade_references_cachoid_manager(): void
    {
        $this->assertInstanceOf(EloquentAdapter::class, Facade::eloquent());
    }
}
