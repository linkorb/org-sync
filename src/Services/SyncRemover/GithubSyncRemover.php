<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Services\SyncRemover;

use Github\Client;
use LinkORB\OrgSync\DTO\Organization;

class GithubSyncRemover implements SyncRemoverInterface
{
    /**
     * @var Client
     */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function removeNonExists(Organization $organization): void
    {
        $orgUsersToSync = [];
        foreach ($organization->getUsers() as $user) {
            $orgUsersToSync[$user->getUsername()] = $user;
        }

        foreach ($this->client->teams() as $group) {
            $members = $this->client->team()->members($group);

            foreach ($members as $member) {
                if (!isset($orgUsersToSync[$member])) {
                    $this->client->team()->removeMember($group, $member);
                }
            }
        }
    }
}
