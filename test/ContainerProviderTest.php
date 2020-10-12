<?php
declare(strict_types=1);

namespace Clearbooks\Dilex;

use PHPUnit\Framework\TestCase;

class ContainerProviderTest extends TestCase
{
    /**
     * @var ContainerProvider
     */
    private $containerProvider;

    public function setUp(): void
    {
        parent::setUp();
        $this->containerProvider = new ContainerProvider();
    }

    /**
     * @test
     */
    public function GivenContainerNotSet_WhenGettingContainer_ExpectNull()
    {
        $this->assertNull($this->containerProvider->getContainer());
    }

    /**
     * @test
     */
    public function WhenSettingContainer_ThenGettingContainer_ExpectContainerReturned()
    {
        $container = new MockContainer([]);
        $this->containerProvider->setContainer($container);
        $this->assertSame($container, $this->containerProvider->getContainer());
    }
}
