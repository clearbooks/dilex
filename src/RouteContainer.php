<?php
namespace Clearbooks\Dilex;

use Symfony\Component\Routing\Route;

interface RouteContainer
{
    /**
     * Maps a pattern to a callable.
     * You can optionally specify HTTP methods that should be matched.
     * @param string $pattern Matched route pattern
     * @param string $endpoint Endpoint that returns the response when matched
     * @return Route
     */
    public function match( string $pattern, string $endpoint ): Route;

    /**
     * Maps a GET request to a callable.
     * @param string $pattern Matched route pattern
     * @param string $endpoint Endpoint that returns the response when matched
     * @return Route
     */
    public function get( string $pattern, string $endpoint ): Route;

    /**
     * Maps a POST request to a callable.
     * @param string $pattern Matched route pattern
     * @param string $endpoint Endpoint that returns the response when matched
     * @return Route
     */
    public function post( string $pattern, string $endpoint ): Route;

    /**
     * Maps a PUT request to a callable.
     * @param string $pattern Matched route pattern
     * @param string $endpoint Endpoint that returns the response when matched
     * @return Route
     */
    public function put( string $pattern, string $endpoint ): Route;

    /**
     * Maps a DELETE request to a callable.
     * @param string $pattern Matched route pattern
     * @param string $endpoint Endpoint that returns the response when matched
     * @return Route
     */
    public function delete( string $pattern, string $endpoint ): Route;

    /**
     * Maps an OPTIONS request to a callable.
     * @param string $pattern Matched route pattern
     * @param string $endpoint Endpoint that returns the response when matched
     * @return Route
     */
    public function options( string $pattern, string $endpoint ): Route;

    /**
     * Maps a PATCH request to a callable.
     * @param string $pattern Matched route pattern
     * @param string $endpoint Endpoint that returns the response when matched
     * @return Route
     */
    public function patch( string $pattern, string $endpoint ): Route;
}
