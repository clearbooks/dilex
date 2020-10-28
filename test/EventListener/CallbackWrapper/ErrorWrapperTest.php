<?php
declare(strict_types=1);

namespace Clearbooks\Dilex\EventListener\CallbackWrapper;

use Clearbooks\Dilex\ContainerProvider;
use Clearbooks\Dilex\MockContainer;
use Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Throwable;

class ErrorWrapperTest extends TestCase
{
    /**
     * @var MockContainer
     */
    private $mockContainer;

    /**
     * @var ErrorWrapper
     */
    private $errorWrapper;

    public function setUp(): void
    {
        parent::setUp();
        $this->mockContainer = new MockContainer([]);
        $containerProvider = new ContainerProvider();
        $containerProvider->setContainer($this->mockContainer);
        $this->errorWrapper = new ErrorWrapper($containerProvider);
    }

    private function createTestExceptionEvent(Throwable $exception = null): ExceptionEvent
    {
        return new ExceptionEvent(
                $this->createMock(HttpKernelInterface::class),
                new Request(),
                HttpKernelInterface::MASTER_REQUEST,
                $exception ?? new Exception()
        );
    }

    /**
     * @test
     */
    public function WhenCalled_ExpectCallbackCalledWithCorrectParameters()
    {
        $event = $this->createTestExceptionEvent();
        $callback = ErrorCallback::class;
        $callbackInstance = new ErrorCallback();
        $this->mockContainer->setMapping($callback, $callbackInstance);
        $callable = $this->errorWrapper->wrap($callback);
        $callable($event);
        $this->assertSame([[$event->getThrowable(), 500, $event->getRequest()]], $callbackInstance->getCallHistory());
    }

    /**
     * @test
     */
    public function GivenHttpException_WhenCalled_ExpectCallbackCalledWithCorrectCode()
    {
        $exception = new HttpException(404);
        $event = $this->createTestExceptionEvent($exception);
        $callback = ErrorCallback::class;
        $callbackInstance = new ErrorCallback();
        $this->mockContainer->setMapping($callback, $callbackInstance);
        $callable = $this->errorWrapper->wrap($callback);
        $callable($event);
        $this->assertSame([[$event->getThrowable(), $exception->getStatusCode(), $event->getRequest()]], $callbackInstance->getCallHistory());
    }
}
