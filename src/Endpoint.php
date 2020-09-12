<?php
namespace Clearbooks\Dilex;
use Symfony\Component\HttpFoundation\Request;

interface Endpoint
{
    public function execute( Request $request );
}
