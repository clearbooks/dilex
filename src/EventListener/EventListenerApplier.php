<?php
namespace Clearbooks\Dilex\EventListener;

interface EventListenerApplier
{
    public function before( $callback, int $priority = 0 ): void;
    public function after( $callback, int $priority = 0 ): void;
    public function finish( $callback, int $priority = 0 ): void;
    public function error( $callback, int $priority = -8 ): void;
}
