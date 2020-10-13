<?php
namespace Clearbooks\Dilex\EventListener;

use Clearbooks\Dilex\ContainerProvider;
use Clearbooks\Dilex\Endpoint;
use Clearbooks\Dilex\Middleware;
use Clearbooks\Dilex\MiddlewareCallbackResolver;
use RuntimeException;

class CallbackClassResolver
{
    /**
     * @var ContainerProvider
     */
    private $containerProvider;

    /**
     * @var MiddlewareCallbackResolver
     */
    private $middlewareCallbackResolver;

    public function __construct( ContainerProvider $containerProvider )
    {
        $this->containerProvider = $containerProvider;
        $this->middlewareCallbackResolver = new MiddlewareCallbackResolver();
    }

    public function resolve( $callback ): callable
    {
        if ( !is_string( $callback ) && ( !is_array( $callback ) || !is_string( $callback[0] ) ) ) {
            if ( !is_callable( $callback ) ) {
                throw new RuntimeException( 'Invalid callback.' );
            }

            return $callback;
        }

        if ( is_string( $callback ) ) {
            $callback = $this->middlewareCallbackResolver->resolve( $callback );
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
