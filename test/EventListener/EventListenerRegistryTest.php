<?php
declare(strict_types=1);

namespace Clearbooks\Dilex\EventListener;

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

    /**
     * @test
     */
    public function GivenNoEventsAdded_WhenCallingRegisterEvents_ExpectEventDispatcherNotCalled()
    {
        $this->eventDispatcherInterfaceSpy->expects($this->never())->method('addListener');
        $this->eventListenerRegistry->registerEvents($this->eventDispatcherInterfaceSpy);
    }

    /**
     * @test
     */
    public function GivenSomeEventsAdded_WhenCallingRegisterEvents_ExpectEventDispatcherCalledForEachEvent()
    {
        $event1 = new EventListenerRecord(KernelEvents::REQUEST, [$this, 'setUp'], 1);
        $this->eventListenerRegistry->addEvent($event1);

        $event2 = new EventListenerRecord(KernelEvents::RESPONSE, [$this, 'count'], 2);
        $this->eventListenerRegistry->addEvent($event2);

        $this->eventDispatcherInterfaceSpy->expects($this->exactly(2))->method('addListener')->withConsecutive(
                [
                        $event1->getEventType(),
                        $event1->getCallback(),
                        $event1->getPriority()
                ],

                [
                        $event2->getEventType(),
                        $event2->getCallback(),
                        $event2->getPriority()
                ]
        );
        $this->eventListenerRegistry->registerEvents($this->eventDispatcherInterfaceSpy);
    }
}
