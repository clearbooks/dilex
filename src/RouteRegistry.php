<?php

declare(strict_types=1);

namespace Clearbooks\Dilex;

class RouteRegistry
{
    private EndpointCallbackResolver $endpointCallbackResolver;

    /**
     * @var Route[]
     */
    private array $routes = [];

    public function __construct()
    {
        $this->endpointCallbackResolver = new EndpointCallbackResolver();
    }

    private function createRoute( string $pattern, string $endpoint, ?string $method = null ): Route
    {
        return new Route(
            path: $pattern,
            controller: $this->endpointCallbackResolver->resolve( $endpoint ),
            methods: $method === null ? [] : [$method]
        );
    }

    public function addRoute( string $pattern, string $endpoint, ?string $method = null ): Route
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
