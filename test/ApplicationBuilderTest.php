<?php

namespace Clearbooks\Dilex;

use PHPUnit\Framework\TestCase;
use TomVerran\MockContainer;

class ApplicationBuilderTest extends TestCase
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
