<?php
declare(strict_types=1);

namespace Clearbooks\Dilex\EventListener\CallbackWrapper;

use Clearbooks\Dilex\ContainerProvider;
use Clearbooks\Dilex\MockContainer;
use Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Throwable;

class FinishWrapperTest extends TestCase
{
    /**
     * @var MockContainer
     */
    private $mockContainer;

    /**
     * @var FinishWrapper
     */
    private $finishWrapper;

    public function setUp(): void
    {
        parent::setUp();
        $this->mockContainer = new MockContainer([]);
        $containerProvider = new ContainerProvider();
        $containerProvider->setContainer($this->mockContainer);
        $this->finishWrapper = new FinishWrapper($containerProvider);
    }

    private function createTestTerminateEvent(): TerminateEvent
    {
        return new TerminateEvent(
                $this->createMock(HttpKernelInterface::class),
                new Request(),
                new Response()
        );
    }

    /**
     * @test
     */
    public function WhenCalled_ExpectCallbackCalledWithCorrectParameters()
    {
        $event = $this->createTestTerminateEvent();
        $callback = FinishCallback::class;
        $callbackInstance = new FinishCallback();
        $this->mockContainer->setMapping($callback, $callbackInstance);
        $callable = $this->finishWrapper->wrap($callback);
        $callable($event);
        $this->assertSame([[$event->getRequest(), $event->getResponse()]], $callbackInstance->getCallHistory());
    }
}
