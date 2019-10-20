<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Services\SyncRemover;

use Gnello\Mattermost\Driver;
use LinkORB\OrgSync\DTO\Group;
use LinkORB\OrgSync\DTO\Organization;
use LinkORB\OrgSync\Services\Mattermost\BaseEntriesProvider;

class MattermostSyncRemover implements SyncRemoverInterface
{
    /** @var Driver */
    private $driver;

    /** @var BaseEntriesProvider */
    private $provider;

    public function __construct(Driver $driver, BaseEntriesProvider $provider)
    {
        $this->driver = $driver;
        $this->provider = $provider;
    }

    public function removeNonExists(Organization $organization): void
    {
        $existingUsers = $this->provider->getExistingUsers();
        $existingGroups = $this->provider->getExistingGroups();

        // Removing users
        $syncUsers = [];
        foreach ($organization->getUsers() as $user) {
            $syncUsers[$user->getUsername()] = $user;
        }

        $existingUsersIds = [];
        foreach ($existingUsers as $existingUser) {
            $existingUsersIds[$existingUser['username']] = $existingUser['id'];

            if (!isset($syncUsers[$existingUser['username']])) {
                $this->driver->getUserModel()->deactivateUserAccount($existingUser['id']);
            }
        }

        // Removing groups
        $syncGroups = [];
        foreach ($organization->getGroups() as $group) {
            $syncGroups[$group->getName()] = $group;
        }

        foreach ($existingGroups as $existingGroup) {
            if (!isset($syncGroups[$existingGroup['name']])) {
                $this->driver->getTeamModel()->deleteTeam($existingGroup['id']);

                continue;
            }

            $membersMap = [];
            /** @var Group $group */
            $group = $syncGroups[$existingGroup['name']];
            foreach ($group->getMembers() as $member) {
                $membersMap[$existingUsersIds[$member->getUsername()]] = true;
            }

            foreach ($this->provider->getTeamMembers($existingGroup['id']) as $member) {
                if (!isset($membersMap[$member])) {
                    $this->driver->getTeamModel()->removeUser($existingGroup['id'], $member, []);
                }
            }
        }
    }
}
