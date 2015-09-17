<?php
namespace Clearbooks\Dilex;
use Interop\Container\ContainerInterface;
use Silex\Application;

/**
 * Thanks for providing an interface, Silex
 * Class CallbackResolver
 * @package Framework
 */
class CallbackResolver extends \Silex\CallbackResolver
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct( ContainerInterface $container, Application $app )
    {
        parent::__construct( $app );
        $this->container = $container;
    }

    /**
     * Returns a callable given its string representation.
     *
     * @param string $name
     *
     * @return array A callable array
     *
     * @throws \InvalidArgumentException In case the method does not exist.
     */
    public function resolveCallback($name)
    {
        if ( !is_callable( $callback = parent::resolveCallback( $name ) ) ) {
            if ( !class_exists( $name ) || !in_array( Middleware::class, class_implements( $name ) ) ) {
                throw new \Exception( $name . ' is not a valid middleware class' );
            }
            $callback = [ $this->container->get( $name ), 'execute' ];
        }
        return $callback;
    }
}