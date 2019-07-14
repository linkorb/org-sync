<?php declare(strict_types=1);

namespace LinkORB\OrgSync\SynchronizationAdapter\UserPush;

use Github\Client;
use LinkORB\OrgSync\DTO\User;

class GithubUserPushAdapter implements UserPushInterface
{
    /**
     * @var Client
     */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function pushUser(User $user): UserPushInterface
    {

        return $this;
    }
}
