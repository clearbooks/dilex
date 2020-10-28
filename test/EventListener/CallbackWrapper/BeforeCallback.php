<?php
declare(strict_types=1);

namespace Clearbooks\Dilex\EventListener\CallbackWrapper;

use Clearbooks\Dilex\CallHistory;
use Symfony\Component\HttpFoundation\Request;

class BeforeCallback
{
    use CallHistory;

    /**
     * @var mixed
     */
    private $result = null;

    public function __invoke(Request $request)
    {
        $this->callHistory[] = $request;
        return $this->result;
    }

    /**
     * @param mixed $result
     */
    public function setResult( $result ): void
    {
        $this->result = $result;
    }
}
