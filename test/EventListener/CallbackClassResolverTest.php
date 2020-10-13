<?php
declare(strict_types=1);

namespace Clearbooks\Dilex\EventListener;

use Clearbooks\Dilex\ContainerProvider;
use Clearbooks\Dilex\MockContainer;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class CallbackClassResolverTest extends TestCase
{
    /**
     * @var MockContainer
     */
    private $mockContainer;

    /**
     * @var CallbackClassResolver
     */
    private $callbackClassResolver;

    public function setUp(): void
    {
        parent::setUp();
        $this->mockContainer = new MockContainer([]);
        $containerProvider = new ContainerProvider();
        $containerProvider->setContainer($this->mockContainer);
        $this->callbackClassResolver = new CallbackClassResolver($containerProvider);
    }

    /**
     * @test
     */
    public function WhenPassingNonCallableObject_ExpectException()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid callback.');
        $this->callbackClassResolver->resolve(new \stdClass());
    }

    /**
     * @test
     */
    public function WhenPassingCallableObject_ExpectSameObjectReturned()
    {
        $callable = new CallableDummy();
        $result = $this->callbackClassResolver->resolve($callable);
        $this->assertSame($callable, $result);
    }

    /**
     * @test
     */
    public function WhenPassingCallableArray_AndFirstArrayParameterIsObject_ExpectSameArrayReturned()
    {
        $callable = [new CallableDummy(), 'execute'];
        $result = $this->callbackClassResolver->resolve($callable);
        $this->assertSame($callable, $result);
    }

    /**
     * @test
     */
    public function WhenPassingCallableArray_AndFirstArrayParameterIsString_ExpectClassResolved()
    {
        $callable = [CallableDummy::class, 'execute'];
        $callableDummyInstance = new CallableDummy();
        $this->mockContainer->setMapping(CallableDummy::class, $callableDummyInstance);
        $result = $this->callbackClassResolver->resolve($callable);
        $this->assertSame([$callableDummyInstance, 'execute'], $result);
    }

    /**
     * @test
     */
    public function WhenPassingNonCallableStringWithDoubleColon_ExpectException()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid callback.');
        $callable = 'hello::test';
        $this->callbackClassResolver->resolve($callable);
    }

    /**
     * @test
     */
    public function WhenPassingCallableStringWithDoubleColon_ExpectCallableStringReturnedAsItIs()
    {
        $callable = CallableDummy::class . '::run';
        $result = $this->callbackClassResolver->resolve($callable);
        $this->assertSame($callable, $result);
    }

    /**
     * @test
     */
    public function WhenPassingStringWithoutDoubleColon_ExpectClassResolved()
    {
        $callable = CallableDummy::class;
        $callableDummyInstance = new CallableDummy();
        $this->mockContainer->setMapping(CallableDummy::class, $callableDummyInstance);
        $result = $this->callbackClassResolver->resolve($callable);
        $this->assertSame($callableDummyInstance, $result);
    }
}
