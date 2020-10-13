<?php
declare(strict_types=1);

namespace Clearbooks\Dilex;

class Counter
{
    /**
     * @var int
     */
    private $cnt = 0;

    public function increaseByOne()
    {
        ++$this->cnt;
    }

    public function get(): int
    {
        return $this->cnt;
    }
}
