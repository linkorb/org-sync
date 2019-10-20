<?php declare(strict_types=1);

namespace LinkORB\OrgSync\SynchronizationAdapter\GroupPush;

use LinkORB\OrgSync\DTO\Group;
use LinkORB\OrgSync\Services\Ldap\Client;
use LinkORB\OrgSync\Services\Ldap\LdapAssertionAwareTrait;
use LinkORB\OrgSync\Services\Ldap\LdapParentHelper;
use LinkORB\OrgSync\SynchronizationAdapter\UserPush\LdapUserPushAdapter;

class LdapGroupPushAdapter implements GroupPushInterface
{
    public const GROUPS_ORG_UNIT = 'groups';

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

    public function pushGroup(Group $group): GroupPushInterface
    {
        $groupInfo = $this->getGroupInfo($group);

        $groupsOrgUnit = $this->client->count(
            $this->client->search(sprintf('(ou=%s)', static::GROUPS_ORG_UNIT))
        );

        $this->assertResult($groupsOrgUnit !== null, 'Error during search!');
        if ($groupsOrgUnit === 0) {
            $this->client->add(
                [
                    'ou' => static::GROUPS_ORG_UNIT,
                    'objectClass' => ['top', 'organizationalUnit'],
                ],
                ['ou' => static::GROUPS_ORG_UNIT]
            );
        }

        $groupRn = ['cn' => $this->parentHelper->getParentGroups([], $group), 'ou' => [static::GROUPS_ORG_UNIT]];

        $groupFirst = $this->client->first(
            $this->client->search(sprintf('(cn=%s)', $group->getName()), $groupRn)
        );

        if (!$groupFirst) {
            array_unshift($groupRn['cn'], $group->getName());
            $res = $this->client->add($groupInfo, $groupRn);
        } else {
            $res = $this->client->modify($groupInfo, $this->client->getDn($groupFirst));
        }

        $this->assertResult((bool)$res, sprintf('Group \'%s\' wasn\'t added', $group->getName()));

        return $this;
    }

    private function getGroupInfo(Group $group): array
    {
        $groupInfo = [
            'cn' => $group->getName(),
            'objectClass' => ['top', 'groupOfUniqueNames'],
            'uniqueMember' => [],
        ];

        foreach ($group->getMembers() as $member) {
            $groupInfo['uniqueMember'][] = $this->client->generateDn(
                ['uid' => $member->getUsername(), 'ou' => LdapUserPushAdapter::USERS_ORG_UNIT]
            );
        }

        return $groupInfo;
    }
}
