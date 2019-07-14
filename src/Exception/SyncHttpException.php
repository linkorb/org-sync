<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Exception;

use Exception;
use Throwable;

class SyncHttpException extends Exception
{
    public function __construct(Throwable $previous)
    {
        parent::__construct('', 0, $previous);
    }
}