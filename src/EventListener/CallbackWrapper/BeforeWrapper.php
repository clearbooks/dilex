<?php

declare(strict_types=1);

namespace Clearbooks\Dilex\EventListener\CallbackWrapper;

use Clearbooks\Dilex\ContainerProvider;
use Clearbooks\Dilex\EventListener\CallbackClassResolver;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;

use function call_user_func;

class BeforeWrapper implements CallbackWrapper
{
    private CallbackClassResolver $callbackResolver;

    public function __construct( ContainerProvider $containerProvider )
    {
        $this->callbackResolver = new CallbackClassResolver( $containerProvider );
    }

    #[\Override]
    public function wrap( $callback ): callable
    {
        return function( RequestEvent $event ) use ( $callback ) {
            if ( !$event->isMainRequest() ) {
                return;
            }

            $result = call_user_func(
                    $this->callbackResolver->resolve( $callback ),
                    $event->getRequest()
            );

            if ( $result instanceof Response ) {
                $event->setResponse( $result );
            }
        };
    }
}
