<?php

declare(strict_types=1);

namespace Clearbooks\Dilex\EventListener;

use Clearbooks\Dilex\ContainerProvider;
use Clearbooks\Dilex\MiddlewareCallbackResolver;
use RuntimeException;

use function is_string;
use function is_array;
use function is_callable;
use function str_contains;


class CallbackClassResolver
{
    private MiddlewareCallbackResolver $middlewareCallbackResolver;

    public function __construct(
        private readonly ContainerProvider $containerProvider
    ) {
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
            if (str_contains($callback, '::')) {
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
