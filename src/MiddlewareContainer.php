<?php


namespace Clearbooks\Dilex;

use Silex\Application;

interface MiddlewareContainer
{
    /**
     * Registers a before filter.
     *
     * Before filters are run before any route has been matched.
     *
     * @param Middleware $middleware Before filter callback
     * @param int   $priority The higher this value, the earlier an event
     *                        listener will be triggered in the chain (defaults to 0)
     */
    public function before( $middleware, $priority = 0);

    /**
     * Registers an after filter.
     *
     * After filters are run after the controller has been executed.
     *
     * @param Middleware $middleware After filter callback
     * @param int   $priority The higher this value, the earlier an event
     *                        listener will be triggered in the chain (defaults to 0)
     */
    public function after( $middleware, $priority = 0);
}