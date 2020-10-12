<?php
declare(strict_types=1);

namespace Clearbooks\Dilex;

use InvalidArgumentException;
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

    /**
     * @test
     */
    public function GivenNoRoutes_WhenCallingGetRoutes_ExpectEmptyArray()
    {
        $this->assertEquals( [], $this->routeRegistry->getRoutes() );
    }

    /**
     * @test
     */
    public function WhenTryingToAddRoute_AndClassDoesNotImplementEndpoint_ExpectException()
    {
        $invalidEndpoint = InvalidEndpoint::class;
        $this->expectException( InvalidArgumentException::class );
        $this->expectExceptionMessage('Class ' . $invalidEndpoint . ' doesn\'t implement ' . Endpoint::class);
        $this->routeRegistry->addRoute( "/test", $invalidEndpoint );
    }

    /**
     * @test
     */
    public function WhenAddingRoute_ExpectRouteCorrectlyConfigured()
    {
        $routePath = '/test';
        $controller = EndpointDummy::class;
        $method = Request::METHOD_POST;
        $route = $this->routeRegistry->addRoute( $routePath, $controller, $method );
        $this->assertEquals( $routePath, $route->getPath() );
        $this->assertEquals( [ $controller, 'execute' ], $route->getDefault('_controller') );
        $this->assertEquals( [ $method ], $route->getMethods() );
    }

    /**
     * @test
     */
    public function GivenRouteAdded_WhenCallingGetRoutes_ExpectRouteReturned()
    {
        $routePath = '/test';
        $controller = EndpointDummy::class;
        $method = Request::METHOD_POST;
        $route = $this->routeRegistry->addRoute( $routePath, $controller, $method );
        $this->assertEquals( [ $route ], $this->routeRegistry->getRoutes() );
    }
}
