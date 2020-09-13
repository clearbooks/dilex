<?php
namespace Clearbooks\Dilex\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ViewEvent;

class StringToResponseListener
{
    public function execute( ViewEvent $event )
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
