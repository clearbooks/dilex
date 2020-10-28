<?php
declare(strict_types=1);

namespace Clearbooks\Dilex;

use Clearbooks\Dilex\EventListener\CallbackWrapper\AfterCallback;
use PHPUnit\Framework\TestCase;

class EndpointCallbackResolverTest extends TestCase
{
    /**
     * @var EndpointCallbackResolver
     */
    private $endpointCallbackResolver;

    public function setUp(): void
    {
        parent::setUp();
        $this->endpointCallbackResolver = new EndpointCallbackResolver();
    }

    /**
     * @test
     */
    public function WhenCallbackIsNotString_ReturnSameCallback()
    {
        $callback = new \stdClass();
        $newCallback = $this->endpointCallbackResolver->resolve($callback);
        $this->assertSame($callback, $newCallback);
    }

    /**
     * @test
     */
    public function WhenCallbackIsStringButNotAClass_ReturnSameCallback()
    {
        $callback = 'hello';
        $newCallback = $this->endpointCallbackResolver->resolve($callback);
        $this->assertSame($callback, $newCallback);
    }

    /**
     * @test
     */
    public function WhenCallbackIsString_RefersToAClass_ButDoesNotImplementEndpoint_ReturnSameCallback()
    {
        $callback = AfterCallback::class;
        $newCallback = $this->endpointCallbackResolver->resolve($callback);
        $this->assertSame($callback, $newCallback);
    }

    /**
     * @test
     */
    public function WhenCallbackIsString_RefersToAClass_AndImplementsEndpoint_ReturnArrayWithExecuteMethodSpecified()
    {
        $callback = EndpointDummy::class;
        $newCallback = $this->endpointCallbackResolver->resolve($callback);
        $this->assertSame([EndpointDummy::class, 'execute'], $newCallback);
    }
}
