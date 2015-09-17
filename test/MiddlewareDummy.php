<?php
namespace Clearbooks\Dilex;
use Symfony\Component\HttpFoundation\Request;

class MiddlewareDummy implements Middleware
{
    /**
     * @param Request $request
     */
    public function execute( Request $request )
    {
        // do nothing.
    }
}