<?php
declare(strict_types=1);

namespace Clearbooks\Dilex\EventListener;

class CallableDummy
{
    public function __invoke()
    {
    }

    public function execute(): void
    {
    }

    public static function run(): void
    {
    }
}
