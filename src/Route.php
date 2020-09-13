<?php
namespace Clearbooks\Dilex;

use Symfony\Component\Routing\Route as SymfonyRoute;

class Route extends SymfonyRoute
{
    const OPTION_BEFORE_CONTROLLER_LISTENERS = '_before_controller_listeners';
    const OPTION_AFTER_CONTROLLER_LISTENERS = '_after_controller_listeners';

    public function assert( string $key, string $regex ): self
    {
        return $this->addRequirements( [ $key => $regex ] );
    }

    public function before( callable $callback ): self
    {
        $callbacks = (array)$this->getOption( self::OPTION_BEFORE_CONTROLLER_LISTENERS );
        $callbacks[] = $callback;
        $this->setOption( self::OPTION_BEFORE_CONTROLLER_LISTENERS, $callbacks );
        return $this;
    }

    public function after( callable $callback ): self
    {
        $callbacks = (array)$this->getOption( self::OPTION_AFTER_CONTROLLER_LISTENERS );
        $callbacks[] = $callback;
        $this->setOption( self::OPTION_AFTER_CONTROLLER_LISTENERS, $callbacks );
        return $this;
    }
}
