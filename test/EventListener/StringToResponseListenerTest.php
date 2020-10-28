<?php
declare(strict_types=1);

namespace Clearbooks\Dilex\EventListener;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class StringToResponseListenerTest extends TestCase
{
    /**
     * @var StringToResponseListener
     */
    private $stringToResponseListener;

    public function setUp(): void
    {
        parent::setUp();
        $this->stringToResponseListener = new StringToResponseListener();
    }

    private function createViewEventWithControllerResult($result): ViewEvent
    {

        return new ViewEvent(
                $this->createMock(HttpKernelInterface::class),
                new Request(),
                1,
                $result
        );
    }

    /**
     * @test
     */
    public function GivenNullControllerResult_ExpectResponseUnchanged()
    {
        $event = $this->createViewEventWithControllerResult(null);
        $this->stringToResponseListener->execute($event);
        $this->assertFalse($event->hasResponse());
    }

    /**
     * @test
     */
    public function GivenArrayControllerResult_ExpectResponseUnchanged()
    {
        $event = $this->createViewEventWithControllerResult([]);
        $this->stringToResponseListener->execute($event);
        $this->assertFalse($event->hasResponse());
    }

    /**
     * @test
     */
    public function GivenResponseTypeControllerResult_ExpectResponseUnchanged()
    {
        $event = $this->createViewEventWithControllerResult(new Response());
        $this->stringToResponseListener->execute($event);
        $this->assertFalse($event->hasResponse());
    }

    /**
     * @test
     */
    public function GivenObjectTypeControllerResult_WithoutToStringMethod_ExpectResponseUnchanged()
    {
        $event = $this->createViewEventWithControllerResult(new \stdClass());
        $this->stringToResponseListener->execute($event);
        $this->assertFalse($event->hasResponse());
    }

    /**
     * @test
     */
    public function GivenStringControllerResult_ExpectResponseSet()
    {
        $result = "test";
        $event = $this->createViewEventWithControllerResult($result);
        $this->stringToResponseListener->execute($event);
        $this->assertEquals(new Response($result), $event->getResponse());
    }

    /**
     * @test
     */
    public function GivenObjectTypeControllerResult_WithToStringMethod_ExpectResponseUnchanged()
    {
        $result = new TestResultWithToStringMethod();
        $event = $this->createViewEventWithControllerResult($result);
        $this->stringToResponseListener->execute($event);
        $this->assertEquals(new Response((string)$result), $event->getResponse());
    }
}
