<?php
declare(strict_types=1);

namespace Clearbooks\Dilex\EventListener\CallbackWrapper;

use Clearbooks\Dilex\CallHistory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FinishCallback
{
    use CallHistory;

    public function __invoke(Request $request, Response $response)
    {
        $this->callHistory[] = [$request, $response];
    }
}
