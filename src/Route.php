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

    private function addCallback( string $option, callable $callback ): void
    {
        $callbacks = (array)$this->getOption( $option );
        $callbacks[] = $callback;
        $this->setOption( $option, $callbacks );
    }

    public function before( callable $callback ): self
    {
        $this->addCallback( self::OPTION_BEFORE_CONTROLLER_LISTENERS, $callback );
        return $this;
    }

    public function after( callable $callback ): self
    {
        $this->addCallback( self::OPTION_AFTER_CONTROLLER_LISTENERS, $callback );
        return $this;
    }
}
