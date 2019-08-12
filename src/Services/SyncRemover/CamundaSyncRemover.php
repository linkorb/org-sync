<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Services\SyncRemover;

use GuzzleHttp\Client;
use LinkORB\OrgSync\DTO\Group;
use LinkORB\OrgSync\DTO\Organization;
use LinkORB\OrgSync\DTO\User;
use LinkORB\OrgSync\Services\Camunda\CamundaGroupMemberProvider;
use LinkORB\OrgSync\Services\Camunda\CamundaGroupProvider;
use LinkORB\OrgSync\Services\Camunda\CamundaUserProvider;

class CamundaSyncRemover implements SyncRemoverInterface
{
    /**
     * @var CamundaUserProvider
     */
    private $usersProvider;

    /**
     * @var CamundaGroupProvider
     */
    private $groupsProvider;

    /**
     * @var CamundaGroupMemberProvider
     */
    private $userGroupsProvider;

    /**
     * @var Client
     */
    private $httpClient;

    public function __construct(
        CamundaUserProvider $usersProvider,
        CamundaGroupProvider $groupsProvider,
        CamundaGroupMemberProvider $userGroupsProvider,
        Client $httpClient
    ) {
        $this->usersProvider = $usersProvider;
        $this->groupsProvider = $groupsProvider;
        $this->userGroupsProvider = $userGroupsProvider;
        $this->httpClient = $httpClient;
    }

    public function removeNonExists(Organization $organization): void
    {
        $existingUsers = $this->usersProvider->getUsers();
        $existingGroups = $this->groupsProvider->getGroups();

        // Removing users
        $syncUsers = [];
        foreach ($organization->getUsers() as $user) {
            $syncUsers[$user->getUsername()] = $user;
        }

        foreach ($existingUsers as $existingUser) {
            if (!isset($syncUsers[$existingUser->getUsername()])) {
                $this->removeUser($existingUser);
            }
        }

        // Removing groups
        $syncGroups = [];
        foreach ($organization->getGroups() as $group) {
            $syncGroups[$group->getName()] = $group;
        }

        /** @var Group $existingGroup */
        foreach ($existingGroups as $existingGroup) {
            if (!isset($syncGroups[$existingGroup->getName()])) {
                $this->removeGroup($existingGroup);
            }
        }

        // Removing users from groups
        $syncGroupUsers = [];
        foreach ($organization->getGroups() as $group) {
            foreach ($group->getMembers() as $member) {
                $syncGroupUsers[$group->getName()][$member->getUsername()] = true;
            }
        }

        foreach ($existingUsers as $user) {
            if (!isset($syncUsers[$user->getUsername()])) {
                continue;
            }

            $userGroups = $this->userGroupsProvider->getGroupsForUser($user);

            foreach ($userGroups as $group) {
                if (!isset($syncGroups[$group->getName()])) {
                    continue;
                }

                if (!($syncGroupUsers[$group->getName()][$user->getUsername()] ?? false)) {
                    $this->removeMemberFromGroup($user, $group);
                }
            }
        }
    }

    private function removeMemberFromGroup(User $user, Group $group): void
    {
        $this->httpClient->delete(sprintf('group/%s/members/%s', $group->getName(), $user->getUsername()));
    }

    private function removeGroup(Group $group): void
    {
        $this->httpClient->delete(sprintf('group/%s', $group->getName()));
    }

    private function removeUser(User $user): void
    {
        $this->httpClient->delete(sprintf('user/%s', $user->getUsername()));
    }
}
