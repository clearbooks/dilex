<?php
declare(strict_types=1);

namespace Clearbooks\Dilex;

use PHPUnit\Framework\Attributes\Test;
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

    #[Test]
    public function GivenContainerNotSet_WhenGettingContainer_ExpectNull()
    {
        $this->assertNull($this->containerProvider->getContainer());
    }

    #[Test]
    public function WhenSettingContainer_ThenGettingContainer_ExpectContainerReturned()
    {
        $container = new MockContainer([]);
        $this->containerProvider->setContainer($container);
        $this->assertSame($container, $this->containerProvider->getContainer());
    }
}
