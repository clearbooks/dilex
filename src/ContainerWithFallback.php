<?php
namespace Clearbooks\Dilex;

use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Container;

class ContainerWithFallback extends Container
{
    /**
     * @var ContainerInterface
     */
    private $fallbackContainer;

    public function setFallbackContainer( ContainerInterface $fallbackContainer ): void
    {
        $this->fallbackContainer = $fallbackContainer;
    }

    public function has( $id )
    {
        if ( parent::has( $id ) ) {
            return true;
        }

        if ( !$this->fallbackContainer ) {
            return false;
        }

        return $this->fallbackContainer->has( $id );
    }

    public function get( $id, $invalidBehavior = self::EXCEPTION_ON_INVALID_REFERENCE )
    {
        if ( !$this->fallbackContainer || parent::has( $id ) ) {
            return parent::get( $id );
        }

        // TODO: support $invalidBehavior?
        return $this->fallbackContainer->get( $id );
    }
}
