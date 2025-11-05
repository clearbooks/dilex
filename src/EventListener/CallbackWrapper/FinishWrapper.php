<?php

declare(strict_types=1);

namespace Clearbooks\Dilex\EventListener\CallbackWrapper;

use Clearbooks\Dilex\ContainerProvider;
use Clearbooks\Dilex\EventListener\CallbackClassResolver;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

use function call_user_func;

class FinishWrapper implements CallbackWrapper
{
    private CallbackClassResolver $callbackResolver;

    public function __construct( ContainerProvider $containerProvider )
    {
        $this->callbackResolver = new CallbackClassResolver( $containerProvider );
    }

    #[\Override]
    public function wrap( $callback ): callable
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
