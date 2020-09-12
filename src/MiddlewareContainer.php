<?php
namespace Clearbooks\Dilex;

interface MiddlewareContainer
{
    /**
     * Registers a before filter.
     *
     * Before filters are run before any route has been matched.
     *
     * @param string $middleware Before filter callback (a class that implements Middleware)
     * @param int   $priority The higher this value, the earlier an event
     *                        listener will be triggered in the chain (defaults to 0)
     */
    public function before( string $middleware, int $priority = 0 ): void;
}
