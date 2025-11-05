<?php

declare(strict_types=1);

namespace Clearbooks\Dilex;

use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Container;

class ContainerWithFallback extends Container
{
    private ?ContainerInterface $fallbackContainer = null;

    public function setFallbackContainer(
        ContainerInterface $fallbackContainer
    ): void {
        $this->fallbackContainer = $fallbackContainer;
    }

    #[\Override]
    public function has( string $id ): bool
    {
        if ( parent::has( $id ) ) {
            return true;
        }

        if ( !$this->fallbackContainer ) {
            return false;
        }

        return $this->fallbackContainer->has( $id );
    }

    #[\Override]
    public function get( string $id, int $invalidBehavior = self::EXCEPTION_ON_INVALID_REFERENCE ): ?object
    {
        if ( !$this->fallbackContainer || parent::has( $id ) ) {
            return parent::get( $id );
        }

        // TODO: support $invalidBehavior?
        return $this->fallbackContainer->get( $id );
    }
}
