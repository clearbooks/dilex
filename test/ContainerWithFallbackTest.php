<?php
declare(strict_types=1);

namespace Clearbooks\Dilex;

use PHPUnit\Framework\TestCase;

class ContainerWithFallbackTest extends TestCase
{
    /**
     * @var ContainerWithFallback
     */
    private $containerWithFallback;

    public function setUp(): void
    {
        parent::setUp();
        $this->containerWithFallback = new ContainerWithFallback();
    }

    /**
     * @test
     */
    public function GivenHasFallbackContainer_WhenCallingHasAndFallbackContainerHasItem_ExpectTrue()
    {
        $fallbackContainer = new MockContainer([Endpoint::class => new EndpointDummy()]);
        $this->containerWithFallback->setFallbackContainer($fallbackContainer);
        $this->assertTrue($this->containerWithFallback->has(Endpoint::class));
    }

    /**
     * @test
     */
    public function GivenHasFallbackContainer_WhenCallingGetAndFallbackContainerHasItem_ExpectItemReturned()
    {
        $endpoint = new EndpointDummy();
        $fallbackContainer = new MockContainer([Endpoint::class => $endpoint]);
        $this->containerWithFallback->setFallbackContainer($fallbackContainer);
        $this->assertSame($endpoint, $this->containerWithFallback->get(Endpoint::class));
    }
}
