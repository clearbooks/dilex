<?php
declare(strict_types=1);

namespace Clearbooks\Dilex\EventListener\CallbackWrapper;

use Clearbooks\Dilex\ContainerProvider;
use Clearbooks\Dilex\MockContainer;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class AfterWrapperTest extends TestCase
{
    /**
     * @var MockContainer
     */
    private $mockContainer;

    /**
     * @var AfterWrapper
     */
    private $afterWrapper;

    public function setUp(): void
    {
        parent::setUp();
        $this->mockContainer = new MockContainer([]);
        $containerProvider = new ContainerProvider();
        $containerProvider->setContainer($this->mockContainer);
        $this->afterWrapper = new AfterWrapper($containerProvider);
    }

    private function createTestResponseEvent(int $requestType = HttpKernelInterface::MASTER_REQUEST): ResponseEvent
    {
        return new ResponseEvent(
                $this->createMock(HttpKernelInterface::class),
                new Request(),
                $requestType,
                new Response()
        );
    }

    /**
     * @test
     */
    public function WhenCallbackReturnsNotAResponseOrNull_ExpectException()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid after middleware response.');

        $event = $this->createTestResponseEvent();
        $callback = AfterCallback::class;
        $callbackInstance = new AfterCallback();
        $callbackInstance->setResult('');
        $this->mockContainer->setMapping($callback, $callbackInstance);
        $callable = $this->afterWrapper->wrap($callback);
        $callable($event);
    }

    /**
     * @test
     */
    public function WhenCallbackReturnsResponse_ExpectResponseSetOnEvent()
    {
        $event = $this->createTestResponseEvent();
        $callback = AfterCallback::class;
        $callbackInstance = new AfterCallback();
        $response = new Response('test');
        $callbackInstance->setResult($response);
        $this->mockContainer->setMapping($callback, $callbackInstance);
        $callable = $this->afterWrapper->wrap($callback);
        $callable($event);
        $this->assertSame($response, $event->getResponse());
    }

    /**
     * @test
     */
    public function WhenCalled_ExpectCallbackCalledWithCorrectParameters()
    {
        $event = $this->createTestResponseEvent();
        $callback = AfterCallback::class;
        $callbackInstance = new AfterCallback();
        $this->mockContainer->setMapping($callback, $callbackInstance);
        $callable = $this->afterWrapper->wrap($callback);
        $callable($event);
        $this->assertSame([[$event->getRequest(), $event->getResponse()]], $callbackInstance->getCallHistory());
    }

    /**
     * @test
     */
    public function WhenCallbackReturnsNull_ExpectOriginalResponseNotChanged()
    {
        $event = $this->createTestResponseEvent();
        $originalResponse = $event->getResponse();
        $callback = AfterCallback::class;
        $callbackInstance = new AfterCallback();
        $this->mockContainer->setMapping($callback, $callbackInstance);
        $callable = $this->afterWrapper->wrap($callback);
        $callable($event);
        $this->assertSame($originalResponse, $event->getResponse());
    }

    /**
     * @test
     */
    public function WhenEventIsNotMasterRequest_ExpectNothingCalledOrChanged()
    {
        $event = $this->createTestResponseEvent(HttpKernelInterface::SUB_REQUEST);
        $originalResponse = $event->getResponse();
        $callback = AfterCallback::class;
        $callbackInstance = new AfterCallback();
        $this->mockContainer->setMapping($callback, $callbackInstance);
        $callable = $this->afterWrapper->wrap($callback);
        $callable($event);
        $this->assertEquals([], $callbackInstance->getCallHistory());
        $this->assertSame($originalResponse, $event->getResponse());
    }
}
