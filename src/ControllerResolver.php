<?php
namespace Clearbooks\Dilex;
use Interop\Container\ContainerInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ControllerResolver
 */
class ControllerResolver extends \Symfony\Component\HttpKernel\Controller\ControllerResolver
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct( ContainerInterface $container )
    {
        parent::__construct();
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
