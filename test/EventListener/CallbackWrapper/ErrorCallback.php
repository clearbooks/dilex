<?php
declare(strict_types=1);

namespace Clearbooks\Dilex\EventListener\CallbackWrapper;

use Clearbooks\Dilex\CallHistory;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

class ErrorCallback
{
    use CallHistory;

    public function __invoke(Throwable $throwable, int $code, Request $request)
    {
        $this->callHistory[] = [$throwable, $code, $request];
    }
}
