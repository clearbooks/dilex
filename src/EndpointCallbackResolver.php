<?php
declare(strict_types=1);

namespace Clearbooks\Dilex;

class EndpointCallbackResolver
{
    public function resolve( $callback )
    {
        if ( !is_string( $callback )
             || !class_exists( $callback )
             || !in_array( Endpoint::class, class_implements( $callback ) ) ) {
            return $callback;
        }

        return [ $callback, 'execute' ];
    }
}
