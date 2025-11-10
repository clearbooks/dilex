<?php
declare(strict_types=1);

namespace Clearbooks\Dilex;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class RouteRegistryTest extends TestCase
{
    /**
     * @var RouteRegistry
     */
    private $routeRegistry;

    public function setUp(): void
    {
        parent::setUp();
        $this->routeRegistry = new RouteRegistry();
    }

    #[Test]
    public function GivenNoRoutes_WhenCallingGetRoutes_ExpectEmptyArray()
    {
        $this->assertEquals( [], $this->routeRegistry->getRoutes() );
    }

    #[Test]
    public function WhenAddingRoute_ExpectRouteCorrectlyConfigured()
    {
        $routePath = '/test';
        $controller = EndpointDummy::class;
        $method = Request::METHOD_POST;
        $route = $this->routeRegistry->addRoute( $routePath, $controller, $method );
        $this->assertEquals( $routePath, $route->getPath() );
        $this->assertEquals( [ $controller, 'execute' ], $route->getController() );
        $this->assertEquals( [ $method ], $route->getMethods() );
    }

    #[Test]
    public function GivenRouteAdded_WhenCallingGetRoutes_ExpectRouteReturned()
    {
        $routePath = '/test';
        $controller = EndpointDummy::class;
        $method = Request::METHOD_POST;
        $route = $this->routeRegistry->addRoute( $routePath, $controller, $method );
        $this->assertEquals( [ $route ], $this->routeRegistry->getRoutes() );
    }
}
