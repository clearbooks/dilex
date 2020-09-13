<?php
namespace Clearbooks\Dilex\EventListener;

interface EventListenerApplier
{
    public function before( callable $callable, int $priority = 0 ): void;
    public function after( callable $callback, int $priority = 0 ): void;
    public function finish( callable $callback, int $priority = 0 ): void;
    public function error( callable $callback, int $priority = -8 ): void;
}
