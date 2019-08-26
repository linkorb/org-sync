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
        $groupsToSync = [];
        foreach ($organization->getGroups() as $group) {
            $groupsToSync[$group->getName()] = $group;
        }

        $orgUsersToSync = [];
        foreach ($organization->getUsers() as $user) {
            $orgUsersToSync[$user->getUsername()] = $user;
        }

        foreach ($this->client->teams()->all($organization->getName()) as $group) {
            $teamName = $group['name'];

            if (!isset($groupsToSync[$teamName])) {
                $this->client->team()->remove($teamName);

                continue;
            }

            $members = $this->client->team()->members($teamName);

            foreach ($members as $member) {
                if (!isset($orgUsersToSync[$member])) {
                    $this->client->team()->removeMember($teamName, $member);
                }
            }
        }
    }
}
