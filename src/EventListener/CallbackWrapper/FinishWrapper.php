<?php
namespace Clearbooks\Dilex\EventListener\CallbackWrapper;

use Clearbooks\Dilex\ContainerProvider;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

class FinishWrapper implements CallbackWrapper
{
    /**
     * @var ContainerProvider
     */
    private $containerProvider;

    /**
     * @var CallbackClassResolver
     */
    private $callbackResolver;

    public function __construct( ContainerProvider $containerProvider )
    {
        $this->containerProvider = $containerProvider;
        $this->callbackResolver = new CallbackClassResolver( $containerProvider );
    }

    public function wrap( callable $callback ): callable
    {
        return function( TerminateEvent $event ) use ( $callback ) {
            call_user_func(
                    $this->callbackResolver->resolve( $callback ),
                    $event->getRequest(),
                    $event->getResponse()
            );
        };
    }
}
