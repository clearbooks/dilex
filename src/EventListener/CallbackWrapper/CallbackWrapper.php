<?php

declare(strict_types=1);

namespace Clearbooks\Dilex\EventListener\CallbackWrapper;

interface CallbackWrapper
{
    public function wrap( $callback ): callable;
}
