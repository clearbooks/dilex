<?php
namespace Clearbooks\Dilex;
use Symfony\Component\HttpFoundation\Request;

/**
 * Interface Middleware
 * @package Framework
 */
interface Middleware
{
    public function execute( Request $request );
}