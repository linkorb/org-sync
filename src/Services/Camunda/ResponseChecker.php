<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Services\Camunda;

use LinkORB\OrgSync\Exception\UserSyncException;
use Psr\Http\Message\ResponseInterface;

class ResponseChecker
{
    public function assertResponse(ResponseInterface $response): void
    {
        if ($response->getStatusCode() >= 400) {
            throw new UserSyncException((string) $response->getBody(), $response->getStatusCode());
        }
    }
}