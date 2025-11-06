<?php
declare(strict_types=1);

namespace Clearbooks\Dilex\EventListener;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class EventListenerRegistryTest extends TestCase
{
    /**
     * @var EventListenerRegistry
     */
    private $eventListenerRegistry;

    /**
     * @var EventDispatcherInterface|MockObject
     */
    private $eventDispatcherInterfaceSpy;

    public function setUp(): void
    {
        parent::setUp();
        $this->eventListenerRegistry = new EventListenerRegistry();
        $this->eventDispatcherInterfaceSpy = $this->createMock(EventDispatcherInterface::class);
    }

    #[Test]
    public function GivenNoEventsAdded_WhenCallingRegisterEvents_ExpectEventDispatcherNotCalled()
    {
        $this->eventDispatcherInterfaceSpy->expects($this->never())->method('addListener');
        $this->eventListenerRegistry->registerEvents($this->eventDispatcherInterfaceSpy);
    }

    #[Test]
    public function GivenSomeEventsAdded_WhenCallingRegisterEvents_ExpectEventDispatcherCalledForEachEvent()
    {
        $event1 = new EventListenerRecord(KernelEvents::REQUEST, [$this, 'setUp'], 1);
        $this->eventListenerRegistry->addEvent($event1);

        $event2 = new EventListenerRecord(KernelEvents::RESPONSE, [$this, 'count'], 2);
        $this->eventListenerRegistry->addEvent($event2);

        $this->eventDispatcherInterfaceSpy->expects($matcher = $this->exactly(2))->method('addListener')->willReturnCallback(function (...$x) use ($matcher, $event1, $event2) {
            match ($matcher->numberOfInvocations()) {
                1 => self::assertEquals(
                    [
                        $event1->getEventType(),
                        $event1->getCallback(),
                        $event1->getPriority()
                    ],
                    $x
                ),
                2 => self::assertEquals(
                    [
                        $event2->getEventType(),
                        $event2->getCallback(),
                        $event2->getPriority()
                    ],
                    $x
                )
            };
        });

        $this->eventListenerRegistry->registerEvents($this->eventDispatcherInterfaceSpy);
    }
}
