<?php

declare(strict_types=1);

namespace Clearbooks\Dilex\EventListener\CallbackWrapper;

use Clearbooks\Dilex\ContainerProvider;
use Clearbooks\Dilex\EventListener\CallbackClassResolver;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

use function call_user_func;

class AfterWrapper implements CallbackWrapper
{
    private CallbackClassResolver $callbackResolver;

    public function __construct(
        ContainerProvider $containerProvider
    ) {
        $this->callbackResolver = new CallbackClassResolver( $containerProvider );
    }

    #[\Override]
    public function wrap( $callback ): callable
    {
        return function( ResponseEvent $event ) use ( $callback ) {
            if ( !$event->isMainRequest() ) {
                return;
            }

            $result = call_user_func(
                    $this->callbackResolver->resolve( $callback ),
                    $event->getRequest(),
                    $event->getResponse()
            );

            if ( $result instanceof Response ) {
                $event->setResponse( $result );
            } else if ( $result !== null ) {
                throw new RuntimeException( 'Invalid after middleware response.' );
            }
        };
    }
}
