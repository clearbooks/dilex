<?php
namespace Clearbooks\Dilex;
use PHPUnit\Framework\TestCase;
use stdClass;
use TomVerran\MockContainer;

class CallbackResolverTest extends TestCase
{
    /**
     * @var MockContainer
     */
    private $mockContainer;

    /**
     * @var CallbackResolver
     */
    private $resolver;

    /**
     * @var Dilex
     */
    private $app;

    /**
     * @param $callback
     * @return array
     */
    private function resolve( $callback )
    {
        return $this->resolver->resolveCallback( $callback );
    }

    /**
     * Set up
     */
    public function setUp(): void
    {
        $this->app = new Dilex;
        $this->mockContainer = new MockContainer( [ MiddlewareDummy::class => new MiddlewareDummy ] );
        $this->resolver = new CallbackResolver( $this->mockContainer, $this->app );
    }

    /**
     * @test
     */
    public function givenNullName_throwException()
    {
        $this->expectException(\Exception::class);
        $this->resolve( null );
    }

    /**
     * @test
     */
    public function givenClassWhichIsNotMiddleware_throwException()
    {
        $this->expectException(\Exception::class);
        $this->resolve( stdClass::class );
    }

    /**
     * @test
     */
    public function givenClosure_returnClosure()
    {
        $closure = function() {
            echo 'cats';
        };
        $this->assertEquals( $closure, $this->resolve( $closure ) );
    }

    /**
     * @test
     */
    public function givenCallable_returnCallable()
    {
        $callable = [ $this, 'setUp' ];
        $this->assertEquals( $callable, $this->resolve( $callable ) );
    }


    /**
     * @test
     */
    public function givenClassWhichIsMiddleware_returnArrayOfObjectAndMethod()
    {
        $this->assertEquals([new MiddlewareDummy(), 'execute'],  $this->resolve( MiddlewareDummy::class ) );
    }
}
