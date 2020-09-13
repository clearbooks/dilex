<?php
namespace Clearbooks\Dilex;

use Clearbooks\Dilex\EventListener\EventListenerRegistry;
use InvalidArgumentException;

class RouteRegistry
{
    /**
     * @var Route[]
     */
    private $routes = [];

    private function checkEndpoint( string $endpoint ): void
    {
        if ( !in_array( Endpoint::class, class_implements( $endpoint ) ) ) {
            throw new InvalidArgumentException(
                    'Class ' . $endpoint . ' doesn\'t implement ' . Endpoint::class
            );
        }
    }

    private function createRoute( string $pattern, string $endpoint, string $method = null ): Route
    {
        $this->checkEndpoint( $endpoint );
        $route = new Route( $pattern );
        $route->setDefault( '_controller', [ $endpoint, 'execute' ] );
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
