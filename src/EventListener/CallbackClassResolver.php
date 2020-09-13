<?php
namespace Clearbooks\Dilex\EventListener;

use Clearbooks\Dilex\ContainerProvider;
use RuntimeException;

class CallbackClassResolver
{
    /**
     * @var ContainerProvider
     */
    private $containerProvider;

    public function __construct( ContainerProvider $containerProvider )
    {
        $this->containerProvider = $containerProvider;
    }

    public function resolve( $callback ): callable
    {
        if ( !is_string( $callback ) && ( !is_array( $callback ) || !is_string( $callback[0] ) ) ) {
            if ( !is_callable( $callback ) ) {
                throw new RuntimeException( 'Invalid callback.' );
            }

            return $callback;
        }

        if ( is_array( $callback ) ) {
            $callback[0] = $this->containerProvider->getContainer()->get( $callback[0] );
        }
        else {
            if ( strpos( $callback, '::' ) !== false ) {
                if ( !is_callable( $callback ) ) {
                    throw new RuntimeException( 'Invalid callback.' );
                }

                return $callback;
            }

            $callback = $this->containerProvider->getContainer()->get( $callback );
        }

        return $callback;
    }
}
