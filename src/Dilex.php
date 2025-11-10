<?php

declare(strict_types=1);

namespace Clearbooks\Dilex;

use Clearbooks\Dilex\EventListener\CallbackWrapper\AfterWrapper;
use Clearbooks\Dilex\EventListener\CallbackWrapper\BeforeWrapper;
use Clearbooks\Dilex\EventListener\CallbackWrapper\ErrorWrapper;
use Clearbooks\Dilex\EventListener\CallbackWrapper\FinishWrapper;
use Clearbooks\Dilex\EventListener\EventListenerApplier;
use Clearbooks\Dilex\EventListener\EventListenerRecord;
use Clearbooks\Dilex\EventListener\EventListenerRegistry;
use Clearbooks\Dilex\EventListener\ListenerRunner\AfterControllerListenerRunner;
use Clearbooks\Dilex\EventListener\ListenerRunner\BeforeControllerListenerRunner;
use Clearbooks\Dilex\EventListener\StringToResponseListener;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

use function trim;
use function rtrim;

class Dilex extends Kernel implements RouteContainer, EventListenerApplier
{
    use MicroKernelTrait;

    private RouteRegistry $routeRegistry;
    private ContainerProvider $containerProvider;
    private EventListenerRegistry $eventListenerRegistry;
    private BeforeWrapper $beforeEventListenerWrapper;
    private AfterWrapper $afterEventListenerWrapper;
    private FinishWrapper $finishEventListenerWrapper;
    private ErrorWrapper $errorEventListenerWrapper;
    private BeforeControllerListenerRunner $beforeControllerListenerRunner;
    private AfterControllerListenerRunner $afterControllerListenerRunner;
    private StringToResponseListener $stringToResponseListener;
    private ?string $projectDirectory = null;
    private ?string $cacheDirectory = null;
    private ?string $logDirectory = null;

    public function __construct(
        string $environment,
        bool $debug,
        private readonly ?ContainerInterface $fallbackContainerInterface = null
    ) {
        parent::__construct( $environment, $debug );

        $this->routeRegistry = new RouteRegistry();
        $this->containerProvider = new ContainerProvider();
        $this->eventListenerRegistry = new EventListenerRegistry();
        $this->beforeEventListenerWrapper = new BeforeWrapper( $this->containerProvider );
        $this->afterEventListenerWrapper = new AfterWrapper( $this->containerProvider );
        $this->finishEventListenerWrapper = new FinishWrapper( $this->containerProvider );
        $this->errorEventListenerWrapper = new ErrorWrapper( $this->containerProvider );
        $this->beforeControllerListenerRunner = new BeforeControllerListenerRunner( $this->containerProvider );
        $this->afterControllerListenerRunner = new AfterControllerListenerRunner( $this->containerProvider );
        $this->stringToResponseListener = new StringToResponseListener();
    }

    public function setProjectDirectory( ?string $projectDirectory ): void
    {
        $this->projectDirectory = $projectDirectory === null ? null : rtrim( $projectDirectory, '/' );
    }

    public function setCacheDirectory( ?string $cacheDirectory ): void
    {
        $this->cacheDirectory = $cacheDirectory === null ? null : ( '/' . trim( $cacheDirectory, '/' ) . '/' );
    }

    public function setLogDirectory( ?string $logDirectory ): void
    {
        $this->logDirectory = $logDirectory === null ? null : ( '/' . trim( $logDirectory, '/' ) );
    }

    #[\Override]
    public function getProjectDir(): string
    {
        if ( $this->projectDirectory === null ) {
            return parent::getProjectDir();
        }

        return $this->projectDirectory;
    }

    #[\Override]
    public function getCacheDir(): string
    {
        if ( $this->cacheDirectory === null ) {
            return parent::getCacheDir();
        }

        return $this->getProjectDir() . $this->cacheDirectory . $this->getEnvironment();
    }

    #[\Override]
    public function getLogDir(): string
    {
        if ( $this->logDirectory === null ) {
            return parent::getLogDir();
        }

        return $this->getProjectDir() . $this->logDirectory;
    }

    #[\Override]
    public function registerBundles(): array
    {
        return [
            new FrameworkBundle()
        ];
    }

    protected function configureContainer( ContainerBuilder $container, LoaderInterface $loader ): void
    {

    }

    protected function configureRoutes( RoutingConfigurator $routes ): void
    {
        foreach ( $this->routeRegistry->getRoutes() as $route ) {
            RouteApplier::applyRouteToSymfony($route, $routes);
        }
    }

    #[\Override]
    protected function getContainerBaseClass(): string
    {
        return ContainerWithFallback::class;
    }

    #[\Override]
    protected function initializeContainer(): void
    {
        parent::initializeContainer();

        if ( $this->container instanceof ContainerWithFallback && $this->fallbackContainerInterface ) {
            $this->container->setFallbackContainer( $this->fallbackContainerInterface );
        }
    }

    private function addBeforeControllerListenerRunner(): void
    {
        $this->eventListenerRegistry->addEvent(
                new EventListenerRecord(
                        KernelEvents::REQUEST,
                        [ $this->beforeControllerListenerRunner, 'execute' ],
                        -1024
                )
        );
    }

    private function addAfterControllerListenerRunner(): void
    {
        $this->eventListenerRegistry->addEvent(
                new EventListenerRecord(
                        KernelEvents::RESPONSE,
                        [ $this->afterControllerListenerRunner, 'execute' ],
                        128
                )
        );
    }

    private function initializeListeners(): void
    {
        $this->addBeforeControllerListenerRunner();
        $this->addAfterControllerListenerRunner();
        $this->eventListenerRegistry->addEvent(
                new EventListenerRecord(
                        KernelEvents::VIEW,
                        [ $this->stringToResponseListener, 'execute' ],
                        -10
                )
        );

        /** @var EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = $this->getContainer()->get( 'event_dispatcher' );
        $this->eventListenerRegistry->registerEvents( $eventDispatcher );
    }

    #[\Override]
    public function match( string $pattern, string $endpoint ): Route
    {
        return $this->routeRegistry->addRoute( $pattern, $endpoint );
    }

    #[\Override]
    public function get( string $pattern, string $endpoint ): Route
    {
        return $this->routeRegistry->addRoute( $pattern, $endpoint, Request::METHOD_GET );
    }

    #[\Override]
    public function post( string $pattern, string $endpoint ): Route
    {
        return $this->routeRegistry->addRoute( $pattern, $endpoint, Request::METHOD_POST );
    }

    #[\Override]
    public function put( string $pattern, string $endpoint ): Route
    {
        return $this->routeRegistry->addRoute( $pattern, $endpoint, Request::METHOD_PUT );
    }

    #[\Override]
    public function delete( string $pattern, string $endpoint ): Route
    {
        return $this->routeRegistry->addRoute( $pattern, $endpoint, Request::METHOD_DELETE );
    }

    #[\Override]
    public function options( string $pattern, string $endpoint ): Route
    {
        return $this->routeRegistry->addRoute( $pattern, $endpoint, Request::METHOD_OPTIONS );
    }

    #[\Override]
    public function patch( string $pattern, string $endpoint ): Route
    {
        return $this->routeRegistry->addRoute( $pattern, $endpoint, Request::METHOD_PATCH );
    }

    #[\Override]
    public function before( $callback, int $priority = 0 ): void
    {
        $this->eventListenerRegistry->addEvent(
                new EventListenerRecord(
                        KernelEvents::REQUEST,
                        $this->beforeEventListenerWrapper->wrap( $callback ),
                        $priority
                )
        );
    }

    #[\Override]
    public function after( $callback, int $priority = 0 ): void
    {
        $this->eventListenerRegistry->addEvent(
                new EventListenerRecord(
                        KernelEvents::RESPONSE,
                        $this->afterEventListenerWrapper->wrap( $callback ),
                        $priority
                )
        );
    }

    #[\Override]
    public function finish( $callback, int $priority = 0 ): void
    {
        $this->eventListenerRegistry->addEvent(
                new EventListenerRecord(
                        KernelEvents::TERMINATE,
                        $this->finishEventListenerWrapper->wrap( $callback ),
                        $priority
                )
        );
    }

    #[\Override]
    public function error( $callback, int $priority = -8 ): void
    {
        $this->eventListenerRegistry->addEvent(
                new EventListenerRecord(
                        KernelEvents::EXCEPTION,
                        $this->errorEventListenerWrapper->wrap( $callback ),
                        $priority
                )
        );
    }

    public function run( ?Request $request = null ): void
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
