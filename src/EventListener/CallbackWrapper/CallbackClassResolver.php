<?php
namespace Clearbooks\Dilex\EventListener\CallbackWrapper;

use Clearbooks\Dilex\ContainerProvider;

class CallbackClassResolver
{
    /**
     * @var ContainerProvider
     */
    private $containerProvider;

    /**
     * @var StringCallbackTransformer
     */
    private $stringCallbackTransformer;

    public function __construct( ContainerProvider $containerProvider )
    {
        $this->containerProvider = $containerProvider;
        $this->stringCallbackTransformer = new StringCallbackTransformer( $this->containerProvider );
    }

    public function resolve( callable $callback ): callable
    {
        if ( is_string( $callback ) ) {
            $callback = $this->stringCallbackTransformer->transform( $callback );
        }

        if ( !is_string( $callback ) && ( !is_array( $callback ) || !is_string( $callback[0] ) ) ) {
            return $callback;
        }

        if ( is_array( $callback ) ) {
            $callback[0] = $this->containerProvider->getContainer()->get( $callback[0] );
        }
        else {
            $callback = $this->containerProvider->getContainer()->get( $callback );
        }

        return $callback;
    }
}
