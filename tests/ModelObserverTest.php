<?php

namespace Beep\Cachoid\Tests;

class ModelObserverTest extends TestCase
{

    /**
     * Setup the Test Case.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        User::setCachoidManager($this->manager);
    }

    /**
     * Tear down the Test Case.
     *
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();

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
        $actual = $this->manager->eloquent()->get($this->hash("eloquent:users:{$user->id}"));

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

        $actual = $this->manager->eloquent()->get("users:{$user->id}");

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
}
