<?php

declare(strict_types=1);

namespace Clearbooks\Dilex;

use function is_string;
use function class_exists;
use function class_implements;
use function in_array;

class EndpointCallbackResolver
{
    public function resolve( $callback )
    {
        if ( !is_string( $callback )
             || !class_exists( $callback )
             || !in_array( Endpoint::class, class_implements( $callback ), true ) ) {
            return $callback;
        }

        return [ $callback, 'execute' ];
    }
}
