<?php
declare(strict_types=1);

namespace Clearbooks\Dilex\EventListener\ListenerRunner;

use Clearbooks\Dilex\ContainerProvider;
use Clearbooks\Dilex\EventListener\CallbackWrapper\AfterCallback;
use Clearbooks\Dilex\MockContainer;
use Clearbooks\Dilex\Route;
use Clearbooks\Dilex\RouteApplier;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Routing\Loader\PhpFileLoader;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

class AfterControllerListenerRunnerTest extends TestCase
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
     * @var AfterControllerListenerRunner
     */
    private $afterControllerListenerRunner;

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
        $this->afterControllerListenerRunner = new AfterControllerListenerRunner($containerProvider);
    }

    private function createTestResponseEvent(string $route): ResponseEvent
    {
        $request = new Request();
        $request->attributes->set('_route', $route);
        return new ResponseEvent(
                $this->createMock(HttpKernelInterface::class),
                $request,
                HttpKernelInterface::MAIN_REQUEST,
                new Response()
        );
    }

    #[Test]
    public function GivenRouteDoesNotExist_ExpectNoError()
    {
        $this->expectNotToPerformAssertions();
        $routeName = '/test';
        $event = $this->createTestResponseEvent($routeName);
        $this->afterControllerListenerRunner->execute($event);
    }

    #[Test]
    public function GivenRouteExist_ButNoAfterControllerListeners_ExpectNoError()
    {
        $this->expectNotToPerformAssertions();
        $route = new Route('/test', '');
        RouteApplier::applyRouteToSymfony($route, $this->routingConfigurator);
        $event = $this->createTestResponseEvent($route->getName());
        $this->afterControllerListenerRunner->execute($event);
    }

    #[Test]
    public function GivenRouteExistWithAfterControllerListener_ExpectListenerCalledWithCorrectParameters()
    {
        $callback = AfterCallback::class;
        $callbackInstance = new AfterCallback();
        $this->mockContainer->setMapping($callback, $callbackInstance);

        $route = new Route('/test', '');
        $route->after($callback);

        RouteApplier::applyRouteToSymfony($route, $this->routingConfigurator);
        $event = $this->createTestResponseEvent($route->getName());
        $this->afterControllerListenerRunner->execute($event);

        $this->assertSame([[$event->getRequest(), $event->getResponse()]], $callbackInstance->getCallHistory());
    }

    #[Test]
    public function GivenRouteExistWithAfterControllerListener_WhenCallbackReturnsNotAResponseOrNull_ExpectException()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid after controller middleware response.');

        $callback = AfterCallback::class;
        $callbackInstance = new AfterCallback();
        $callbackInstance->setResult('');
        $this->mockContainer->setMapping($callback, $callbackInstance);

        $route = new Route('/test', '');
        $route->after($callback);

        RouteApplier::applyRouteToSymfony($route, $this->routingConfigurator);
        $event = $this->createTestResponseEvent($route->getName());
        $this->afterControllerListenerRunner->execute($event);
    }

    #[Test]
    public function GivenRouteExistWithAfterControllerListener_WhenCallbackReturnsResponse_ExpectResponseSetOnEvent()
    {
        $callback = AfterCallback::class;
        $callbackInstance = new AfterCallback();
        $response = new Response('test');
        $callbackInstance->setResult($response);
        $this->mockContainer->setMapping($callback, $callbackInstance);

        $route = new Route('/test', '');
        $route->after($callback);

        RouteApplier::applyRouteToSymfony($route, $this->routingConfigurator);
        $event = $this->createTestResponseEvent($route->getName());
        $this->afterControllerListenerRunner->execute($event);

        $this->assertSame($response, $event->getResponse());
    }

    #[Test]
    public function GivenRouteExistWithMultipleAfterControllerListeners_ExpectListenerCalledWithCorrectParametersForEachListener()
    {
        $callback = AfterCallback::class;
        $callbackInstance = new AfterCallback();
        $this->mockContainer->setMapping($callback, $callbackInstance);

        $route = new Route('/test', '');
        $route->after($callback);
        $route->after($callback);
        $route->after($callback);

        RouteApplier::applyRouteToSymfony($route, $this->routingConfigurator);

        $event = $this->createTestResponseEvent($route->getName());
        $this->afterControllerListenerRunner->execute($event);

        $this->assertSame(
                [
                        [$event->getRequest(), $event->getResponse()],
                        [$event->getRequest(), $event->getResponse()],
                        [$event->getRequest(), $event->getResponse()]
                ],
                $callbackInstance->getCallHistory()
        );
    }
}
