<?php
namespace Clearbooks\Dilex\EventListener;

class EventListenerRecord
{
    /**
     * @var string
     */
    private $eventType;

    /**
     * @var callback
     */
    private $callback;

    /**
     * @var int
     */
    private $priority;

    public function __construct( string $eventType, callable $callback, int $priority )
    {
        $this->eventType = $eventType;
        $this->callback = $callback;
        $this->priority = $priority;
    }

    public function getEventType(): string
    {
        return $this->eventType;
    }

    public function getCallback(): callable
    {
        return $this->callback;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }
}
