<?php


namespace Clearbooks\Dilex;

use Symfony\Component\HttpKernel\Tests\Controller;
use TomVerran\MockContainer;

class ApplicationBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function givenApplication_setControllerAndCallbackResolver()
    {
        $app = new Dilex();
        ApplicationBuilder::build( new MockContainer( [] ), $app );
        $this->assertInstanceOf( CallbackResolver::class, $app['callback_resolver'] );
        $this->assertInstanceOf( ControllerResolver::class, $app['resolver'] );
    }
}
