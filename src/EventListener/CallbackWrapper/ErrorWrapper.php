<?php
namespace Clearbooks\Dilex\EventListener\CallbackWrapper;

use Clearbooks\Dilex\ContainerProvider;
use Clearbooks\Dilex\EventListener\CallbackClassResolver;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ErrorWrapper implements CallbackWrapper
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
        return function( ExceptionEvent $event ) use ( $callback ) {
            $exception = $event->getThrowable();
            $code = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500;
            $result = call_user_func(
                    $this->callbackResolver->resolve( $callback ),
                    $event->getThrowable(),
                    $code,
                    $event->getRequest()
            );

            if ( $result instanceof Response ) {
                $event->setResponse( $result );
            }
        };
    }
}
