<?php

namespace Beep\Cachoid\Tests;

use Beep\Cachoid\CachoidManager;
use Beep\Cachoid\EloquentAdapter;

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
    public function setUp(): void
    {
        parent::setUp();
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

        $this->assertTrue($eloquent->has($this->hash('eloquent:tests:bar')));
    }
}
