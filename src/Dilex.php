<?php

namespace Clearbooks\Dilex;

use Exception;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouteCollectionBuilder;

class Dilex extends Kernel implements RouteContainer, MiddlewareContainer
{
    use MicroKernelTrait;

    /**
     * @var ContainerInterface|null
     */
    private $fallbackContainerInterface;

    /**
     * @var array
     */
    private $routes = [];

    /**
     * @var array
     */
    private $beforeRequestListeners = [];

    /**
     * @var array
     */
    private $afterRequestListeners = [];

    public function __construct( string $environment, bool $debug,
                                 ContainerInterface $fallbackContainerInterface = null )
    {
        parent::__construct( $environment, $debug );
        $this->fallbackContainerInterface = $fallbackContainerInterface;
    }

    public function registerBundles(): array
    {
        return [
            new FrameworkBundle()
        ];
    }

    protected function configureContainer( ContainerBuilder $container, LoaderInterface $loader ): void
    {

    }

    protected function configureRoutes( RouteCollectionBuilder $routes ): void
    {
        foreach ( $this->routes as $route ) {
            $routes->addRoute( $route );
        }
    }

    private function checkEndpoint( string $endpoint ): void
    {
        if ( !in_array( Endpoint::class, class_implements( $endpoint ) ) ) {
            throw new InvalidArgumentException(
                    'Class ' . $endpoint . ' doesn\'t implement ' . Endpoint::class
            );
        }
    }

    private function checkBeforeRequestListener( string $beforeRequestListener ): void
    {
        if ( !in_array( Middleware::class, class_implements( $beforeRequestListener ) ) ) {
            throw new InvalidArgumentException(
                    'Class ' . $beforeRequestListener . ' doesn\'t implement ' . Middleware::class
            );
        }
    }

    protected function getContainerBaseClass()
    {
        return ContainerWithFallback::class;
    }

    protected function initializeContainer()
    {
        parent::initializeContainer();

        if ( $this->container instanceof ContainerWithFallback && $this->fallbackContainerInterface ) {
            $this->container->setFallbackContainer( $this->fallbackContainerInterface );
        }
    }

    private function createRoute( string $pattern, string $endpoint, string $method = null ): Route
    {
        $this->checkEndpoint( $endpoint );

        $route = new Route( $pattern );
        $route->setDefault( '_controller', $endpoint . '::execute' );
        if ( $method ) {
            $route->setMethods( [ $method ] );
        }
        return $route;
    }

    private function createAndAddRoute( string $pattern, string $endpoint, string $method = null ): Route
    {
        $route = $this->createRoute( $pattern, $endpoint );
        $this->routes[] = $route;
        return $route;
    }

    private function initializeListeners(): void
    {
        /** @var EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = $this->getContainer()->get( EventDispatcherInterface::class );

        foreach ( $this->beforeRequestListeners as [ $beforeRequestListener, $priority ] ) {
            $eventDispatcher->addListener(
                    KernelEvents::REQUEST,
                    function( RequestEvent $event ) use ( $beforeRequestListener ) {
                        if ( !$event->isMasterRequest() ) {
                            return;
                        }

                        /** @var Middleware $callback */
                        $callback = $this->getContainer()->get( $beforeRequestListener );
                        $result = $callback->execute( $event->getRequest() );
                        if ( $result instanceof Response ) {
                            $event->setResponse( $result );
                        }
                    },
                    $priority
            );
        }

        foreach ( $this->afterRequestListeners as [ $afterRequestListener, $priority ] ) {
            $eventDispatcher->addListener(
                    KernelEvents::RESPONSE,
                    function( ResponseEvent $event ) use ( $afterRequestListener ) {
                        if ( !$event->isMasterRequest() ) {
                            return;
                        }

                        if ( is_string( $afterRequestListener ) && strpos( $afterRequestListener, '::' ) !== false ) {
                            [ $class, $method ] = explode( '::', $afterRequestListener, 2 );
                            $afterRequestListener = [ $this->getContainer()->get( $class ), $method ];
                        }

                        $result = call_user_func( $afterRequestListener, $event->getRequest(), $event->getResponse() );
                        if ( $result instanceof Response ) {
                            $event->setResponse( $result );
                        } else if ( $result !== null ) {
                            throw new RuntimeException( 'Invalid after middleware response.' );
                        }
                    },
                    $priority
            );
        }
    }

    public function match( string $pattern, string $endpoint ): Route
    {
        return $this->createAndAddRoute( $pattern, $endpoint );
    }

    public function get( string $pattern, string $endpoint ): Route
    {
        return $this->createAndAddRoute( $pattern, $endpoint, Request::METHOD_GET );
    }

    public function post( string $pattern, string $endpoint ): Route
    {
        return $this->createAndAddRoute( $pattern, $endpoint, Request::METHOD_POST );
    }

    public function put( string $pattern, string $endpoint ): Route
    {
        return $this->createAndAddRoute( $pattern, $endpoint, Request::METHOD_PUT );
    }

    public function delete( string $pattern, string $endpoint ): Route
    {
        return $this->createAndAddRoute( $pattern, $endpoint, Request::METHOD_DELETE );
    }

    public function options( string $pattern, string $endpoint ): Route
    {
        return $this->createAndAddRoute( $pattern, $endpoint, Request::METHOD_OPTIONS );
    }

    public function patch( string $pattern, string $endpoint ): Route
    {
        return $this->createAndAddRoute( $pattern, $endpoint, Request::METHOD_PATCH );
    }

    public function before( string $beforeRequestListener, int $priority = 0 ): void
    {
        $this->checkBeforeRequestListener( $beforeRequestListener );
        $this->beforeRequestListeners[] = [ $beforeRequestListener, $priority ];
    }

    public function after( callable $callback, int $priority = 0 ): void
    {
        $this->afterRequestListeners[] = [ $callback, $priority ];
    }

    /**
     * Handles the request and delivers the response.
     *
     * @param Request|null $request
     * @throws Exception
     */
    public function run( Request $request = null ): void
    {
        if ( !$request ) {
            $request = Request::createFromGlobals();
        }

        $this->boot();
        $this->initializeListeners();
        $response = $this->handle( $request );
        $response->send();
        $this->terminate( $request, $response );
    }
}
