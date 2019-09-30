<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Services\SyncRemover;

use LinkORB\OrgSync\DTO\Group;
use LinkORB\OrgSync\DTO\Organization;
use LinkORB\OrgSync\DTO\User;
use LinkORB\OrgSync\Services\Ldap\Client;
use LinkORB\OrgSync\Services\Ldap\LdapAssertionAwareTrait;
use LinkORB\OrgSync\Services\Ldap\LdapParentHelper;
use LinkORB\OrgSync\SynchronizationAdapter\GroupPush\LdapGroupPushAdapter;
use LinkORB\OrgSync\SynchronizationAdapter\UserPush\LdapUserPushAdapter;

class LdapSyncRemover implements SyncRemoverInterface
{
    use LdapAssertionAwareTrait;

    /** @var Client */
    private $client;

    /** @var LdapParentHelper */
    private $parentHelper;

    public function __construct(Client $client, LdapParentHelper $parentHelper)
    {
        $this->client = $client;
        $this->parentHelper = $parentHelper;
    }

    public function removeNonExists(Organization $organization): void
    {
        $usersOrgUnit = $this->client->count(
            $this->client->search(sprintf('(ou=%s)', LdapUserPushAdapter::USERS_ORG_UNIT))
        );
        $groupsOrgUnit = $this->client->count(
            $this->client->search(sprintf('(ou=%s)', LdapGroupPushAdapter::GROUPS_ORG_UNIT))
        );

        $this->assertResult($usersOrgUnit !== null && $groupsOrgUnit !== null, 'Error during search!');
        if ($usersOrgUnit !== 0) {
            $this->removeExcessUsers($organization->getUsers());
        }

        if ($groupsOrgUnit !== 0) {
            $this->removeExcessGroups($organization->getGroups());
        }
    }

    /** @param User[] $users */
    private function removeExcessUsers(array $users): void
    {
        $existingUsers = $this->client->all(
            $this->client->search('(objectClass=inetOrgPerson)', ['ou' => LdapUserPushAdapter::USERS_ORG_UNIT])
        );

        // Remove `count` key from array
        array_shift($existingUsers);

        // Removing users
        $syncUsers = [];
        foreach ($users as $user) {
            $syncUsers[$user->getUsername()] = $user;
        }

        foreach ($existingUsers as $existingUser) {
            if (!isset($syncUsers[$existingUser['cn'][0]])) {
                $this->client->remove($existingUser['dn']);
            }
        }
    }

    /**
     * @param Group[] $groups
     */
    private function removeExcessGroups(array $groups): void
    {
        $existingGroups = $existingUsers = $this->client->all(
            $this->client->search('(objectClass=groupOfUniqueNames)', ['ou' => LdapGroupPushAdapter::GROUPS_ORG_UNIT])
        );

        // Remove `count` key from array
        array_shift($existingGroups);

        // Removing users
        /** @var Group[] $syncGroups */
        $syncGroups = [];
        foreach ($groups as $group) {
            $syncGroups[$group->getName()] = $group;
        }

        foreach ($existingGroups as $existingGroup) {
            if (!isset($syncGroups[$existingGroup['cn'][0]])) {
                $this->client->remove($existingGroup['dn']);

                continue;
            }

            $syncGroup = $syncGroups[$existingGroup['cn'][0]];
            $groupDn = $this->client->generateDn(
                [
                    'cn' => $this->parentHelper->getParentGroups([$syncGroup->getName()], $syncGroup),
                    'ou' => LdapGroupPushAdapter::GROUPS_ORG_UNIT,
                ]
            );

            if ($groupDn !== $existingGroup['dn']) {
                $this->client->remove($existingGroup['dn']);
            }
        }
    }
}
