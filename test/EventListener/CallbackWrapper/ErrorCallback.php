<?php
declare(strict_types=1);

namespace Clearbooks\Dilex\EventListener\CallbackWrapper;

use Clearbooks\Dilex\CallHistory;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

class ErrorCallback
{
    use CallHistory;

    /**
     * @var mixed
     */
    private $result = null;

    public function __invoke(Throwable $throwable, int $code, Request $request)
    {
        $this->callHistory[] = [$throwable, $code, $request];

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
