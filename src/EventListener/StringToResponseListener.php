<?php

declare(strict_types=1);

namespace Clearbooks\Dilex\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ViewEvent;

use function is_array;
use function is_object;
use function method_exists;

class StringToResponseListener
{
    public function execute( ViewEvent $event ): void
    {
        $response = $event->getControllerResult();
        if ( $response !== null
             && !is_array( $response )
             && !( $response instanceof Response )
             && ( !is_object( $response ) || method_exists( $response, '__toString' ) )
        ) {
            $event->setResponse( new Response( (string)$response ) );
        }
    }
}
