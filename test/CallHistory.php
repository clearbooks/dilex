<?php
declare(strict_types=1);

namespace Clearbooks\Dilex;

trait CallHistory
{
    /**
     * @var array
     */
    private $callHistory = [];

    /**
     * @return array
     */
    public function getCallHistory(): array
    {
        return $this->callHistory;
    }
}
