<?php
declare(strict_types=1);

namespace Clearbooks\Dilex\EventListener;

class TestResultWithToStringMethod
{
    public function __toString()
    {
        return "test";
    }
}
