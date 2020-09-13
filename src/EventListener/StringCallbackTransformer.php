<?php
namespace Clearbooks\Dilex\EventListener;

use Clearbooks\Dilex\ContainerProvider;

class StringCallbackTransformer
{
    /**
     * @var ContainerProvider
     */
    private $containerProvider;

    public function __construct( ContainerProvider $containerProvider )
    {
        $this->containerProvider = $containerProvider;
    }

    public function transform( callable $callback ): callable
    {
        if ( !is_string( $callback ) ) {
            return $callback;
        }

        $container = $this->containerProvider->getContainer();
        if ( strpos( $callback, '::' ) !== false ) {
            [ $class, $method ] = explode( '::', $callback, 2 );
            $callback = [ $container->get( $class ), $method ];
        }

        return $callback;
    }
}
