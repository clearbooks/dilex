<?php
declare(strict_types=1);

namespace Clearbooks\Dilex;

use Symfony\Component\HttpFoundation\Request;

class IncreaseCounterController implements Endpoint
{
    /**
     * @var Counter
     */
    private $counter;

    public function __construct( Counter $counter)
    {
        $this->counter = $counter;
    }

    public function execute( Request $request )
    {
        $this->counter->increaseByOne();
        return '';
    }
}
