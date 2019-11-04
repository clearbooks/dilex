<?php
namespace Clearbooks\Dilex;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\HttpFoundation\Request;
use TomVerran\MockContainer;

class ControllerResolverTest extends TestCase
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
     * @var Dilex
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
        $this->app = new Dilex();
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
        $this->assertFalse( $this->resolve( stdClass::class ) );
    }

    /**
     * @test
     */
    public function givenClassWhichIsAnEndpoint_returnArrayOfObjectAndMethod()
    {
        $this->assertEquals([new EndpointDummy, 'execute'],  $this->resolve( EndpointDummy::class ) );
    }
}
