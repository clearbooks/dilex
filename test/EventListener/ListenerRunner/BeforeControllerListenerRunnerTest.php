<?php
declare(strict_types=1);

namespace Clearbooks\Dilex\EventListener\ListenerRunner;

use Clearbooks\Dilex\ContainerProvider;
use Clearbooks\Dilex\EventListener\CallbackWrapper\BeforeCallback;
use Clearbooks\Dilex\MockContainer;
use Clearbooks\Dilex\Route;
use Clearbooks\Dilex\RouteApplier;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Routing\Loader\PhpFileLoader;
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

    private RoutingConfigurator $routingConfigurator;

    public function setUp(): void
    {
        parent::setUp();

        $this->routeCollection = new RouteCollection();

        $this->routingConfigurator = new RoutingConfigurator(
            $this->routeCollection,
            new PhpFileLoader(new class implements FileLocatorInterface {

                public function locate(string $name, ?string $currentPath = null, bool $first = true): string|array
                {
                    return [];
                }
            }),
            '',
            ''
        );

        $this->routerInterface = $this->createMock(RouterInterface::class);
        $this->routerInterface->method('getRouteCollection')->willReturnCallback(fn () => $this->routeCollection);

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
                HttpKernelInterface::MAIN_REQUEST
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

        $route = new Route('/test', '');
        RouteApplier::applyRouteToSymfony($route, $this->routingConfigurator);
        $event = $this->createTestRequestEvent($route->getName());
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

        $route = new Route('/test', '');
        $route->before($callback);

        RouteApplier::applyRouteToSymfony($route, $this->routingConfigurator);
        $event = $this->createTestRequestEvent($route->getName());
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

        $route = new Route('/test', '');
        $route->before($callback);

        RouteApplier::applyRouteToSymfony($route, $this->routingConfigurator);
        $event = $this->createTestRequestEvent($route->getName());
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

        $route = new Route('/test', '');
        $route->before($callback);

        RouteApplier::applyRouteToSymfony($route, $this->routingConfigurator);
        $event = $this->createTestRequestEvent($route->getName());
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

        $route = new Route('/test', '');
        $route->before($callback);
        $route->before($callback);
        $route->before($callback);

        RouteApplier::applyRouteToSymfony($route, $this->routingConfigurator);
        $event = $this->createTestRequestEvent($route->getName());
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
