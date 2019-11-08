<?php
namespace Clearbooks\Dilex;


use Interop\Container\ContainerInterface;
use Silex\Application;

abstract class ApplicationBuilder
{
    /**
     * @param ContainerInterface $container
     * @param Application $app
     */
    public static function build( ContainerInterface $container, Application $app )
    {
        $app['callback_resolver'] =  new CallbackResolver( $container, $app );
        $app['resolver'] =  new ControllerResolver( $container );
    }
}
