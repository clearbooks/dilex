<?php
declare(strict_types=1);

namespace Clearbooks\Dilex;

use Clearbooks\Dilex\EventListener\CallbackWrapper\AfterCallback;
use PHPUnit\Framework\TestCase;

class MiddlewareCallbackResolverTest extends TestCase
{
    /**
     * @var MiddlewareCallbackResolver
     */
    private $middlewareCallbackResolver;

    public function setUp(): void
    {
        parent::setUp();
        $this->middlewareCallbackResolver = new MiddlewareCallbackResolver();
    }

    /**
     * @test
     */
    public function WhenCallbackIsNotString_ReturnSameCallback()
    {
        $callback = new \stdClass();
        $newCallback = $this->middlewareCallbackResolver->resolve($callback);
        $this->assertSame($callback, $newCallback);
    }

    /**
     * @test
     */
    public function WhenCallbackIsStringButNotAClass_ReturnSameCallback()
    {
        $callback = 'hello';
        $newCallback = $this->middlewareCallbackResolver->resolve($callback);
        $this->assertSame($callback, $newCallback);
    }

    /**
     * @test
     */
    public function WhenCallbackIsString_RefersToAClass_ButDoesNotImplementMiddleware_ReturnSameCallback()
    {
        $callback = AfterCallback::class;
        $newCallback = $this->middlewareCallbackResolver->resolve($callback);
        $this->assertSame($callback, $newCallback);
    }

    /**
     * @test
     */
    public function WhenCallbackIsString_RefersToAClass_AndImplementsMiddleware_ReturnArrayWithExecuteMethodSpecified()
    {
        $callback = MiddlewareDummy::class;
        $newCallback = $this->middlewareCallbackResolver->resolve($callback);
        $this->assertSame([MiddlewareDummy::class, 'execute'], $newCallback);
    }
}
