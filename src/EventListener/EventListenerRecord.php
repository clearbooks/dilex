<?php

declare(strict_types=1);

namespace Clearbooks\Dilex\EventListener;

class EventListenerRecord
{
    /**
     * @var callable
     */
    private $callback;

    public function __construct(
        private readonly string $eventType,
        callable $callback,
        private readonly int $priority
    ) {
        $this->callback = $callback;
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
