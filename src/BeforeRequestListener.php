<?php
namespace Clearbooks\Dilex;

use Symfony\Component\HttpFoundation\Request;

interface BeforeRequestListener
{
    public function execute( Request $request );
}
