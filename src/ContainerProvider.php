<?php
declare(strict_types=1);

namespace Clearbooks\Dilex;

use Psr\Container\ContainerInterface;

class ContainerProvider
{
    /**
     * @var ContainerInterface
     */
    private $container = null;

    public function getContainer(): ?ContainerInterface
    {
        return $this->container;
    }

    public function setContainer( ContainerInterface $container ): void
    {
        $this->container = $container;
    }
}
