<?php

declare(strict_types=1);

namespace Clearbooks\Dilex;

use Symfony\Component\HttpFoundation\Request;

class ErrorThrowingController implements Endpoint
{
    public function execute(Request $request)
    {
        throw new \RuntimeException('Test exception');
    }
}
