<?php
namespace Clearbooks\Dilex\EventListener\CallbackWrapper;

interface CallbackWrapper
{
    public function wrap( $callback ): callable;
}
