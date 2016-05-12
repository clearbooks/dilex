<?php


namespace Clearbooks\Dilex;

use Silex\Application;

class Dilex extends Application implements RouteContainer, MiddlewareContainer
{
    /**
     * @param string $pattern
     * @param Endpoint $to
     * @return \Silex\Controller
     */
    public function get( $pattern, $to = null )
    {
        return parent::get( $pattern, $to );
    }
    
    /**
     * @param string $pattern
     * @param Endpoint $to
     * @return \Silex\Controller
     */
    public function post( $pattern, $to = null )
    {
        return parent::post( $pattern, $to );
    }

    /**
     * @param string $pattern
     * @param Endpoint $to
     * @return \Silex\Controller
     */
    public function put( $pattern, $to = null )
    {
        return parent::put( $pattern, $to );
    }

    /**
     * @param string $pattern
     * @param Endpoint $to
     * @return \Silex\Controller
     */
    public function options( $pattern, $to = null )
    {
        return parent::options( $pattern, $to );
    }

    /**
     * @param string $pattern
     * @param Endpoint $to
     * @return \Silex\Controller
     */
    public function patch( $pattern, $to = null )
    {
        return parent::patch( $pattern, $to );
    }

    /**
     * @param string $pattern
     * @param Endpoint $to
     * @return \Silex\Controller
     */
    public function match( $pattern, $to = null )
    {
        return parent::match( $pattern, $to );
    }

    /**
     * @param string $pattern
     * @param Endpoint $to
     * @return \Silex\Controller
     */
    public function delete( $pattern, $to = null )
    {
        return parent::delete( $pattern, $to );
    }

    /**
     * @param Middleware $middleware
     * @param int $priority
     */
    public function before( $middleware, $priority = 0 )
    {
        parent::before( $middleware, $priority );
    }

    /**
     * @param Middleware $middleware
     * @param int $priority
     */
    public function after( $middleware, $priority = 0 )
    {
        parent::after( $middleware, $priority );
    }
}