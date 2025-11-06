<?php

declare(strict_types=1);

namespace Clearbooks\Dilex;

use Closure;

use function sort;
use function implode;

class Route
{
    private array $beforeCallbacks = [];
    private array $afterCallbacks = [];

    public function __construct(
        protected string $path,
        protected string|array|Closure $controller,
        protected array $requirements = [],
        protected array $methods = [],
    ) {}


    public function assert( string $key, string $regex ): self
    {
        $this->requirements[$key] = $regex;

        return $this;
    }

    public function before( string|array|Closure $callback ): self
    {
        $this->beforeCallbacks[] = $callback;

        return $this;
    }

    public function after( string|array|Closure $callback ): self
    {
        $this->afterCallbacks[] = $callback;

        return $this;
    }

    public function getBeforeCallbacks(): array
    {
        return $this->beforeCallbacks;
    }

    public function getAfterCallbacks(): array
    {
        return $this->afterCallbacks;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getController(): array|Closure|string
    {
        return $this->controller;
    }

    public function getRequirements(): array
    {
        return $this->requirements;
    }

    public function getMethods(): array
    {
        return $this->methods;
    }

    public function getName(): string
    {
        $methods = $this->methods;

        sort($methods);

        return implode('_', [...$methods, $this->getPath()]);
    }
}
