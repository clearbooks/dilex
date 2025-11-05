<?php

declare(strict_types=1);

namespace Clearbooks\Dilex;

use Symfony\Component\HttpFoundation\Request;

interface Middleware
{
    public function execute( Request $request );
}
