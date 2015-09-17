<?php
namespace Clearbooks\Dilex;
use Interop\Container\ContainerInterface;
use Silex\Application;

/**
 * Class ControllerResolver
 */
class ControllerResolver extends \Silex\ControllerResolver
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param Application $app
     * @param ContainerInterface $container
     */
    public function __construct( Application $app, ContainerInterface $container )
    {
        parent::__construct( $app );
        $this->container = $container;
    }

    /**
     * @param string $controller
     * @return mixed|void
     */
    protected function createController( $controller )
    {
        if ( !in_array( Endpoint::class, class_implements( $controller ) ) ) {
            return false;
        }
        return [$this->container->get( $controller ), 'execute'];
    }
}