<?php
declare(strict_types=1);

namespace Clearbooks\Dilex\EventListener\CallbackWrapper;

use Clearbooks\Dilex\CallHistory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AfterCallback
{
    use CallHistory;

    /**
     * @var mixed
     */
    private $result = null;

    public function __invoke(Request $request, Response $response)
    {
        $this->callHistory[] = [$request, $response];
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
