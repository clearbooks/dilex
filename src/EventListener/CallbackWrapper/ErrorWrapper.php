<?php
namespace Clearbooks\Dilex\EventListener\CallbackWrapper;

use Clearbooks\Dilex\ContainerProvider;
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

    public function wrap( callable $callback ): callable
    {
        return function( ExceptionEvent $event ) use ( $callback ) {
            $exception = $event->getThrowable();
            $code = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500;
            call_user_func(
                    $this->callbackResolver->resolve( $callback ),
                    $event->getThrowable(),
                    $code,
                    $event->getRequest()
            );
        };
    }
}
