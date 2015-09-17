<?php
namespace Clearbooks\Dilex;
use Silex\Application;
use stdClass;
use Symfony\Component\HttpFoundation\Request;
use TomVerran\MockContainer;

class ControllerResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MockContainer
     */
    private $mockContainer;

    /**
     * @var ControllerResolver
     */
    private $resolver;

    /**
     * @var Application
     */
    private $app;

    /**
     * Get a request for the given controller
     * @param $controller
     * @return Request
     */
    private function getRequestWithController( $controller )
    {
        $req = new Request();
        $req->attributes->add( [ '_controller' => $controller ] );
        return $req;
    }

    private function resolve( $controller )
    {
        $req = $this->getRequestWithController( $controller );
        return $this->resolver->getController( $req );
    }

    /**
     * Set up
     */
    public function setUp()
    {
        $this->app = new Application;
        $this->mockContainer = new MockContainer( [ EndpointDummy::class => new EndpointDummy ] );
        $this->resolver = new ControllerResolver( $this->app, $this->mockContainer );
    }

    /**
     * @test
     */
    public function givenNullName_returnFalse()
    {
        $this->assertFalse( $this->resolve( null ) );
    }

    /**
     * @test
     * @expectedException \Exception
     */
    public function givenClassWhichIsNotAnEndpoint_throwException()
    {
        $this->assertFalse( $this->resolve( StdClass::class ) );
    }

    /**
     * @test
     */
    public function givenClassWhichIsAnEndpoint_returnArrayOfObjectAndMethod()
    {
        $this->assertEquals([new EndpointDummy, 'execute'],  $this->resolve( EndpointDummy::class ) );
    }
}
