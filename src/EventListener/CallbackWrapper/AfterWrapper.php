<?php
namespace Clearbooks\Dilex\EventListener\CallbackWrapper;

use Clearbooks\Dilex\ContainerProvider;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class AfterWrapper implements CallbackWrapper
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
        return function( ResponseEvent $event ) use ( $callback ) {
            if ( !$event->isMasterRequest() ) {
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
