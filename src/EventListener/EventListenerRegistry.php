<?php
declare(strict_types=1);

namespace Clearbooks\Dilex\EventListener;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EventListenerRegistry
{
    /**
     * @var EventListenerRecord[]
     */
    private $listeners = [];

    public function addEvent( EventListenerRecord $record ): void
    {
        $this->listeners[] = $record;
    }

    public function registerEvents( EventDispatcherInterface $eventDispatcher ): void
    {
        foreach ( $this->listeners as $listener ) {
            $eventDispatcher->addListener(
                    $listener->getEventType(),
                    $listener->getCallback(),
                    $listener->getPriority()
            );
        }
    }
}
