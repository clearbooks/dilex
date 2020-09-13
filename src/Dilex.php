<?php
namespace Clearbooks\Dilex;

use Clearbooks\Dilex\EventListener\CallbackWrapper\AfterWrapper;
use Clearbooks\Dilex\EventListener\CallbackWrapper\BeforeWrapper;
use Clearbooks\Dilex\EventListener\CallbackWrapper\ErrorWrapper;
use Clearbooks\Dilex\EventListener\CallbackWrapper\FinishWrapper;
use Clearbooks\Dilex\EventListener\EventListenerRecord;
use Clearbooks\Dilex\EventListener\EventListenerRegistry;
use Exception;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouteCollectionBuilder;

class Dilex extends Kernel implements RouteContainer, AddEventListeners
{
    use MicroKernelTrait;

    /**
     * @var ContainerInterface|null
     */
    private $fallbackContainerInterface;

    /**
     * @var RouteRegistry
     */
    private $routeRegistry;

    /**
     * @var ContainerProvider
     */
    private $containerProvider;

    /**
     * @var EventListenerRegistry
     */
    private $eventListenerRegistry;

    /**
     * @var BeforeWrapper
     */
    private $beforeEventListenerWrapper;

    /**
     * @var AfterWrapper
     */
    private $afterEventListenerWrapper;

    /**
     * @var FinishWrapper
     */
    private $finishEventListenerWrapper;

    /**
     * @var ErrorWrapper
     */
    private $errorEventListenerWrapper;

    public function __construct( string $environment, bool $debug,
                                 ContainerInterface $fallbackContainerInterface = null )
    {
        parent::__construct( $environment, $debug );
        $this->fallbackContainerInterface = $fallbackContainerInterface;
        $this->routeRegistry = new RouteRegistry();
        $this->containerProvider = new ContainerProvider();
        $this->eventListenerRegistry = new EventListenerRegistry();
        $this->beforeEventListenerWrapper = new BeforeWrapper( $this->containerProvider );
        $this->afterEventListenerWrapper = new AfterWrapper( $this->containerProvider );
        $this->finishEventListenerWrapper = new FinishWrapper( $this->containerProvider );
        $this->errorEventListenerWrapper = new ErrorWrapper( $this->containerProvider );
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
        foreach ( $this->routeRegistry->getRoutes() as $route ) {
            $routes->addRoute( $route );
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

    private function initializeListeners(): void
    {
        /** @var EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = $this->getContainer()->get( 'event_dispatcher' );
        $this->eventListenerRegistry->registerEvents( $eventDispatcher );
    }

    public function match( string $pattern, string $endpoint ): Route
    {
        return $this->routeRegistry->addRoute( $pattern, $endpoint );
    }

    public function get( string $pattern, string $endpoint ): Route
    {
        return $this->routeRegistry->addRoute( $pattern, $endpoint, Request::METHOD_GET );
    }

    public function post( string $pattern, string $endpoint ): Route
    {
        return $this->routeRegistry->addRoute( $pattern, $endpoint, Request::METHOD_POST );
    }

    public function put( string $pattern, string $endpoint ): Route
    {
        return $this->routeRegistry->addRoute( $pattern, $endpoint, Request::METHOD_PUT );
    }

    public function delete( string $pattern, string $endpoint ): Route
    {
        return $this->routeRegistry->addRoute( $pattern, $endpoint, Request::METHOD_DELETE );
    }

    public function options( string $pattern, string $endpoint ): Route
    {
        return $this->routeRegistry->addRoute( $pattern, $endpoint, Request::METHOD_OPTIONS );
    }

    public function patch( string $pattern, string $endpoint ): Route
    {
        return $this->routeRegistry->addRoute( $pattern, $endpoint, Request::METHOD_PATCH );
    }

    public function before( callable $callback, int $priority = 0 ): void
    {
        $this->eventListenerRegistry->addEvent(
                new EventListenerRecord(
                        KernelEvents::REQUEST,
                        $this->beforeEventListenerWrapper->wrap( $callback ),
                        $priority
                )
        );
    }

    public function after( callable $callback, int $priority = 0 ): void
    {
        $this->eventListenerRegistry->addEvent(
                new EventListenerRecord(
                        KernelEvents::RESPONSE,
                        $this->afterEventListenerWrapper->wrap( $callback ),
                        $priority
                )
        );
    }

    public function finish( callable $callback, int $priority = 0 ): void
    {
        $this->eventListenerRegistry->addEvent(
                new EventListenerRecord(
                        KernelEvents::TERMINATE,
                        $this->finishEventListenerWrapper->wrap( $callback ),
                        $priority
                )
        );
    }

    public function error( callable $callback, int $priority = -8 ): void
    {
        $this->eventListenerRegistry->addEvent(
                new EventListenerRecord(
                        KernelEvents::EXCEPTION,
                        $this->errorEventListenerWrapper->wrap( $callback ),
                        $priority
                )
        );
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
        $this->containerProvider->setContainer( $this->getContainer() );
        $this->initializeListeners();
        $response = $this->handle( $request );
        $response->send();
        $this->terminate( $request, $response );
    }
}
