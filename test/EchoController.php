<?php

declare(strict_types=1);

namespace Clearbooks\Dilex;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EchoController implements Endpoint
{
    public function execute(Request $request)
    {
        return new Response(
            $request->getContent(),
            200
        );
    }
}
