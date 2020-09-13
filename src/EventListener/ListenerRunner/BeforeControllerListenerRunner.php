<?php
namespace Clearbooks\Dilex\EventListener\ListenerRunner;

use Clearbooks\Dilex\ContainerProvider;
use Clearbooks\Dilex\EventListener\CallbackClassResolver;
use Clearbooks\Dilex\Route;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\Router;

class BeforeControllerListenerRunner
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

    public function execute( RequestEvent $event ): void
    {
        $container = $this->containerProvider->getContainer();

        /** @var Router $router */
        $router = $container->get( 'router' );

        $request = $event->getRequest();
        $routeName = $request->attributes->get('_route');
        $route = $router->getRouteCollection()->get( $routeName );
        if ( !$route ) {
            return;
        }

        $callbacks = (array)$route->getOption( Route::OPTION_BEFORE_CONTROLLER_LISTENERS );
        foreach ( $callbacks as $callback ) {
            $result = call_user_func(
                    $this->callbackResolver->resolve( $callback ),
                    $request
            );

            if ( $result instanceof Response ) {
                $event->setResponse( $result );
            } else if ( $result !== null ) {
                throw new RuntimeException( 'Invalid before controller middleware response.' );
            }
        }
    }
}
