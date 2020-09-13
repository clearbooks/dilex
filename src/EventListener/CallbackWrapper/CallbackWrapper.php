<?php
namespace Clearbooks\Dilex\EventListener\CallbackWrapper;

interface CallbackWrapper
{
    public function wrap( callable $callback ): callable;
}
