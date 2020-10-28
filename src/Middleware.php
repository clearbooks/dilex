<?php
namespace Clearbooks\Dilex;

use Symfony\Component\HttpFoundation\Request;

interface Middleware
{
    public function execute( Request $request );
}
