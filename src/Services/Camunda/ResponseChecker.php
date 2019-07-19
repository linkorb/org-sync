<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Services\Camunda;

use LinkORB\OrgSync\DTO\Group;
use LinkORB\OrgSync\DTO\User;
use LinkORB\OrgSync\Exception\GroupSyncException;
use LinkORB\OrgSync\Exception\UserSyncException;
use Psr\Http\Message\ResponseInterface;

class ResponseChecker
{
    public const CONTEXT_MAP = [
        User::class => UserSyncException::class,
        Group::class => GroupSyncException::class,
    ];

    /** @var string */
    private $contextException;

    public function __construct(string $contextDto)
    {
        assert(array_key_exists($contextDto, static::CONTEXT_MAP));

        $this->contextException = static::CONTEXT_MAP[$contextDto];
    }

    public function assertResponse(ResponseInterface $response): void
    {
        if ($response->getStatusCode() >= 400) {
            throw new $this->contextException((string)$response->getBody(), $response->getStatusCode());
        }
    }
}