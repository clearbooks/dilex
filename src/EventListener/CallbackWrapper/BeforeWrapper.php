<?php
namespace Clearbooks\Dilex\EventListener\CallbackWrapper;

use Clearbooks\Dilex\ContainerProvider;
use Clearbooks\Dilex\EventListener\CallbackClassResolver;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class BeforeWrapper implements CallbackWrapper
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

    public function wrap( $callback ): callable
    {
        return function( RequestEvent $event ) use ( $callback ) {
            if ( !$event->isMasterRequest() ) {
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
