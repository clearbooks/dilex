<?php
namespace Clearbooks\Dilex;
use Symfony\Component\HttpFoundation\Request;

class BeforeRequestListenerDummy implements BeforeRequestListener
{
    /**
     * @param Request $request
     */
    public function execute( Request $request )
    {
        // do nothing.
    }
}
