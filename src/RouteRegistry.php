<?php
declare(strict_types=1);

namespace Clearbooks\Dilex;

use InvalidArgumentException;

class RouteRegistry
{
    /**
     * @var EndpointCallbackResolver
     */
    private $endpointCallbackResolver;

    /**
     * @var Route[]
     */
    private $routes = [];

    public function __construct()
    {
        $this->endpointCallbackResolver = new EndpointCallbackResolver();
    }

    private function createRoute( string $pattern, string $endpoint, string $method = null ): Route
    {
        $route = new Route( $pattern );
        $route->setDefault( '_controller', $this->endpointCallbackResolver->resolve( $endpoint ) );
        if ( $method ) {
            $route->setMethods( [ $method ] );
        }
        return $route;
    }

    public function addRoute( string $pattern, string $endpoint, string $method = null ): Route
    {
        $route = $this->createRoute( $pattern, $endpoint, $method );
        $this->routes[] = $route;
        return $route;
    }

    /**
     * @return Route[]
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
}
