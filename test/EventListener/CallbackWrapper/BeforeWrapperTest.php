<?php
declare(strict_types=1);

namespace Clearbooks\Dilex\EventListener\CallbackWrapper;

use Clearbooks\Dilex\ContainerProvider;
use Clearbooks\Dilex\MockContainer;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class BeforeWrapperTest extends TestCase
{
    /**
     * @var MockContainer
     */
    private $mockContainer;

    /**
     * @var BeforeWrapper
     */
    private $beforeWrapper;

    public function setUp(): void
    {
        parent::setUp();
        $this->mockContainer = new MockContainer([]);
        $containerProvider = new ContainerProvider();
        $containerProvider->setContainer($this->mockContainer);
        $this->beforeWrapper = new BeforeWrapper($containerProvider);
    }

    private function createTestRequestEvent(int $requestType = HttpKernelInterface::MASTER_REQUEST): RequestEvent
    {
        return new RequestEvent(
                $this->createMock(HttpKernelInterface::class),
                new Request(),
                $requestType
        );
    }

    /**
     * @test
     */
    public function WhenCallbackReturnsResponse_ExpectResponseSetOnEvent()
    {
        $event = $this->createTestRequestEvent();
        $callback = BeforeCallback::class;
        $callbackInstance = new BeforeCallback();
        $response = new Response('test');
        $callbackInstance->setResult($response);
        $this->mockContainer->setMapping($callback, $callbackInstance);
        $callable = $this->beforeWrapper->wrap($callback);
        $callable($event);
        $this->assertSame($response, $event->getResponse());
    }

    /**
     * @test
     */
    public function WhenCalled_ExpectCallbackCalledWithCorrectParameters()
    {
        $event = $this->createTestRequestEvent();
        $callback = BeforeCallback::class;
        $callbackInstance = new BeforeCallback();
        $this->mockContainer->setMapping($callback, $callbackInstance);
        $callable = $this->beforeWrapper->wrap($callback);
        $callable($event);
        $this->assertSame([$event->getRequest()], $callbackInstance->getCallHistory());
    }

    /**
     * @test
     */
    public function WhenCallbackReturnsNull_ExpectOriginalResponseNotChanged()
    {
        $event = $this->createTestRequestEvent();
        $originalResponse = $event->getResponse();
        $callback = BeforeCallback::class;
        $callbackInstance = new BeforeCallback();
        $this->mockContainer->setMapping($callback, $callbackInstance);
        $callable = $this->beforeWrapper->wrap($callback);
        $callable($event);
        $this->assertSame($originalResponse, $event->getResponse());
    }

    /**
     * @test
     */
    public function WhenEventIsNotMasterRequest_ExpectNothingCalledOrChanged()
    {
        $event = $this->createTestRequestEvent(HttpKernelInterface::SUB_REQUEST);
        $originalResponse = $event->getResponse();
        $callback = BeforeCallback::class;
        $callbackInstance = new BeforeCallback();
        $this->mockContainer->setMapping($callback, $callbackInstance);
        $callable = $this->beforeWrapper->wrap($callback);
        $callable($event);
        $this->assertEquals([], $callbackInstance->getCallHistory());
        $this->assertSame($originalResponse, $event->getResponse());
    }
}
