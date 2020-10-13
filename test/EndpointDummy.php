<?php
namespace Clearbooks\Dilex;

use Symfony\Component\HttpFoundation\Request;

class EndpointDummy implements Endpoint
{
    public function execute( Request $request )
    {
        return '';
    }
}
