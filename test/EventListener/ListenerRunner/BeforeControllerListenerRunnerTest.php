<?php
declare(strict_types=1);

namespace Clearbooks\Dilex\EventListener\ListenerRunner;

use Clearbooks\Dilex\ContainerProvider;
use Clearbooks\Dilex\EventListener\CallbackWrapper\BeforeCallback;
use Clearbooks\Dilex\MockContainer;
use Clearbooks\Dilex\Route;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

class BeforeControllerListenerRunnerTest extends TestCase
{
    /**
     * @var MockContainer
     */
    private $mockContainer;

    /**
     * @var RouteCollection
     */
    private $routeCollection;

    /**
     * @var RouterInterface|MockObject
     */
    private $routerInterface;

    /**
     * @var BeforeControllerListenerRunner
     */
    private $beforeControllerListenerRunner;

    public function setUp(): void
    {
        parent::setUp();

        $this->routeCollection = new RouteCollection();
        $this->routerInterface = $this->createMock(RouterInterface::class);
        $this->routerInterface->method('getRouteCollection')->willReturn($this->routeCollection);

        $this->mockContainer = new MockContainer(['router' => $this->routerInterface]);
        $containerProvider = new ContainerProvider();
        $containerProvider->setContainer($this->mockContainer);
        $this->beforeControllerListenerRunner = new BeforeControllerListenerRunner($containerProvider);
    }

    private function createTestRequestEvent(string $route): RequestEvent
    {
        $request = new Request();
        $request->attributes->set('_route', $route);
        return new RequestEvent(
                $this->createMock(HttpKernelInterface::class),
                $request,
                HttpKernelInterface::MASTER_REQUEST
        );
    }

    /**
     * @test
     */
    public function GivenRouteDoesNotExist_ExpectNoError()
    {
        $this->expectNotToPerformAssertions();
        $routeName = '/test';
        $event = $this->createTestRequestEvent($routeName);
        $this->beforeControllerListenerRunner->execute($event);
    }

    /**
     * @test
     */
    public function GivenRouteExist_ButNoBeforeControllerListeners_ExpectNoError()
    {
        $this->expectNotToPerformAssertions();
        $routeName = '/test';
        $route = new Route($routeName);
        $this->routeCollection->add($routeName, $route);
        $event = $this->createTestRequestEvent($routeName);
        $this->beforeControllerListenerRunner->execute($event);
    }

    /**
     * @test
     */
    public function GivenRouteExistWithBeforeControllerListener_ExpectListenerCalledWithCorrectParameters()
    {
        $callback = BeforeCallback::class;
        $callbackInstance = new BeforeCallback();
        $this->mockContainer->setMapping($callback, $callbackInstance);

        $routeName = '/test';
        $route = new Route($routeName);
        $route->before($callback);

        $this->routeCollection->add($routeName, $route);
        $event = $this->createTestRequestEvent($routeName);
        $this->beforeControllerListenerRunner->execute($event);

        $this->assertSame([$event->getRequest()], $callbackInstance->getCallHistory());
    }

    /**
     * @test
     */
    public function GivenRouteExistWithBeforeControllerListener_WhenCallbackReturnsNotAResponseOrNull_ExpectException()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid before controller middleware response.');

        $callback = BeforeCallback::class;
        $callbackInstance = new BeforeCallback();
        $callbackInstance->setResult('');
        $this->mockContainer->setMapping($callback, $callbackInstance);

        $routeName = '/test';
        $route = new Route($routeName);
        $route->before($callback);

        $this->routeCollection->add($routeName, $route);
        $event = $this->createTestRequestEvent($routeName);
        $this->beforeControllerListenerRunner->execute($event);
    }

    /**
     * @test
     */
    public function GivenRouteExistWithBeforeControllerListener_WhenCallbackReturnsResponse_ExpectResponseSetOnEvent()
    {
        $callback = BeforeCallback::class;
        $callbackInstance = new BeforeCallback();
        $response = new Response('test');
        $callbackInstance->setResult($response);
        $this->mockContainer->setMapping($callback, $callbackInstance);

        $routeName = '/test';
        $route = new Route($routeName);
        $route->before($callback);

        $this->routeCollection->add($routeName, $route);
        $event = $this->createTestRequestEvent($routeName);
        $this->beforeControllerListenerRunner->execute($event);

        $this->assertSame($response, $event->getResponse());
    }

    /**
     * @test
     */
    public function GivenRouteExistWithMultipleBeforeControllerListeners_ExpectListenerCalledWithCorrectParametersForEachListener()
    {
        $callback = BeforeCallback::class;
        $callbackInstance = new BeforeCallback();
        $this->mockContainer->setMapping($callback, $callbackInstance);

        $routeName = '/test';
        $route = new Route($routeName);
        $route->before($callback);
        $route->before($callback);
        $route->before($callback);

        $this->routeCollection->add($routeName, $route);
        $event = $this->createTestRequestEvent($routeName);
        $this->beforeControllerListenerRunner->execute($event);

        $this->assertSame(
                [
                        $event->getRequest(),
                        $event->getRequest(),
                        $event->getRequest()
                ],
                $callbackInstance->getCallHistory()
        );
    }
}
