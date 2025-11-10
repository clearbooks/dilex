<?php

declare(strict_types=1);

namespace Clearbooks\Dilex;

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class RouteApplier
{
    public const string OPTION_BEFORE_CONTROLLER_LISTENERS = '_before_controller_listeners';
    public const string OPTION_AFTER_CONTROLLER_LISTENERS = '_after_controller_listeners';

    public static function applyRouteToSymfony(Route $route, RoutingConfigurator $configurator): void
    {
        $configurator->add($route->getName(), $route->getPath())
            ->controller($route->getController())
            ->methods($route->getMethods())
            ->requirements($route->getRequirements())
            ->options([
                self::OPTION_BEFORE_CONTROLLER_LISTENERS => $route->getBeforeCallbacks(),
                self::OPTION_AFTER_CONTROLLER_LISTENERS => $route->getAfterCallbacks(),
            ]);
    }
}
