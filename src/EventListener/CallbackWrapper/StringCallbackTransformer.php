<?php
namespace Clearbooks\Dilex\EventListener\CallbackWrapper;

use Clearbooks\Dilex\ContainerProvider;
use Clearbooks\Dilex\Middleware;

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
        else if ( $container->has( $callback ) && in_array( Middleware::class, class_implements( $callback ) ) ) {
            $callback = [ $callback, 'execute' ];
        }

        return $callback;
    }
}
