<?php

namespace Beep\Cachoid\Tests;

use Closure;
use Beep\Cachoid\CachoidManager;
use Beep\Cachoid\RouteBindRegistrar;
use Illuminate\Events\Dispatcher;
use Illuminate\Routing\Router;
use Mockery as m;
use Mockery\MockInterface;

class RouteBindRegistrarTest extends TestCase
{
    /**
     * @var MockInterface
     */
    protected $routerMock;

    /**
     * @var MockInterface
     */
    protected $cachoidMock;

    /**
     * @var RouteBindRegistrar
     */
    protected $registrar;

    /**
     * Setup the Test Case.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->routerMock   = m::mock(Router::class);
        $this->cachoidMock  = m::mock(CachoidManager::class);
        $this->registrar    = new RouteBindRegistrar($this->routerMock, $this->cachoidMock);
    }

    /**
     * Tests registering a model binder.
     *
     * @return void
     */
    public function test_registering_a_model_binder(): void
    {
        $user    = new User;
        $key     = 'user';

        $this->routerMock->shouldReceive('bind')->with($key, 'closure')->andReturnUndefined();

        $this->assertNull($this->registrar->model($user, function () {}));
    }

    /**
     * Test binder finds the model.
     *
     * @return void
     */
    public function test_binder_can_find_model(): void
    {
        $expected = tap(new User(['name' => 'Robbie']))->save();

        $binder = (new RouteBindRegistrar(new Router(new Dispatcher), $this->manager))->getBinder(new User);

        $user = $binder($expected->id);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($expected->id, $user->id);
    }
}
