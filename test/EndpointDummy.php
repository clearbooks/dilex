<?php
namespace Clearbooks\Dilex;
use Symfony\Component\HttpFoundation\Request;

class EndpointDummy implements Endpoint
{
    /**
     * @param Request $request
     */
    public function execute( Request $request )
    {
        // do nothing.
    }
}