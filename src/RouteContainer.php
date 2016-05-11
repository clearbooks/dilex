<?php


namespace Clearbooks\Dilex\Dilex;


use Clearbooks\Dilex\Endpoint;
use Silex\Controller;

interface RouteContainer
{
    /**
     * Maps a pattern to a callable.
     *
     * You can optionally specify HTTP methods that should be matched.
     *
     * @param string $pattern Matched route pattern
     * @param Endpoint $to Callback that returns the response when matched
     *
     * @return Controller
     */
    public function match( $pattern, $to = null );

    /**
     * Maps a GET request to a callable.
     *
     * @param string $pattern Matched route pattern
     * @param Endpoint $to Callback that returns the response when matched
     *
     * @return Controller
     */
    public function get( $pattern, $to = null );

    /**
     * Maps a POST request to a callable.
     *
     * @param string $pattern Matched route pattern
     * @param Endpoint $to Callback that returns the response when matched
     *
     * @return Controller
     */
    public function post( $pattern, $to = null );

    /**
     * Maps a PUT request to a callable.
     *
     * @param string $pattern Matched route pattern
     * @param Endpoint $to Callback that returns the response when matched
     *
     * @return Controller
     */
    public function put( $pattern, $to = null );

    /**
     * Maps a DELETE request to a callable.
     *
     * @param string $pattern Matched route pattern
     * @param Endpoint $to Callback that returns the response when matched
     *
     * @return Controller
     */
    public function delete( $pattern, $to = null );

    /**
     * Maps an OPTIONS request to a callable.
     *
     * @param string $pattern Matched route pattern
     * @param Endpoint $to Callback that returns the response when matched
     *
     * @return Controller
     */
    public function options( $pattern, $to = null );

    /**
     * Maps a PATCH request to a callable.
     *
     * @param string $pattern Matched route pattern
     * @param Endpoint $to Callback that returns the response when matched
     *
     * @return Controller
     */
    public function patch( $pattern, $to = null );
}