<?php

declare(strict_types=1);

namespace Clearbooks\Dilex\EventListener\ListenerRunner;

use Clearbooks\Dilex\ContainerProvider;
use Clearbooks\Dilex\EventListener\CallbackClassResolver;
use Clearbooks\Dilex\RouteApplier;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\RouterInterface;

use function call_user_func;

class BeforeControllerListenerRunner
{
    private CallbackClassResolver $callbackResolver;

    public function __construct(
        private readonly ContainerProvider $containerProvider
    ) {
        $this->callbackResolver = new CallbackClassResolver( $containerProvider );
    }

    public function execute( RequestEvent $event ): void
    {
        $container = $this->containerProvider->getContainer();

        /** @var RouterInterface $router */
        $router = $container->get( 'router' );

        $request = $event->getRequest();
        $routeName = $request->attributes->get('_route');
        if ($routeName === null) {
            return;
        }

        $route = $router->getRouteCollection()->get( $routeName );
        if ( !$route ) {
            return;
        }

        $callbacks = (array)$route->getOption( RouteApplier::OPTION_BEFORE_CONTROLLER_LISTENERS );
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
