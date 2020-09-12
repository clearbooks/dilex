<?php
namespace Clearbooks\Dilex;

use Symfony\Component\Routing\Route as SymfonyRoute;

class Route extends SymfonyRoute
{
    public function assert( string $key, string $regex ): self
    {
        return $this->addRequirements( [ $key => $regex ] );
    }

    public function after( callable $callback ): self
    {
        return $this;
    }
}
