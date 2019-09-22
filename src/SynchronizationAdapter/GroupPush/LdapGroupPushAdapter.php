<?php declare(strict_types=1);

namespace LinkORB\OrgSync\SynchronizationAdapter\GroupPush;

use LinkORB\OrgSync\DTO\Group;
use LinkORB\OrgSync\Services\Ldap\Client;
use LinkORB\OrgSync\Services\Ldap\LdapAssertionAwareTrait;
use LinkORB\OrgSync\Services\Ldap\LdapParentHelper;
use LinkORB\OrgSync\Services\Ldap\UserDataMapper;
use LinkORB\OrgSync\SynchronizationAdapter\UserPush\LdapUserPushAdapter;

class LdapGroupPushAdapter implements GroupPushInterface
{
    public const GROUPS_ORG_UNIT = 'groups';

    use LdapAssertionAwareTrait;

    /** @var Client */
    private $client;

    /** @var UserDataMapper */
    private $mapper;

    /** @var LdapParentHelper */
    private $parentHelper;

    public function __construct(Client $client, UserDataMapper $mapper, LdapParentHelper $parentHelper)
    {
        $this->client = $client;
        $this->mapper = $mapper;
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
            $this->client->add([
                'ou' => static::GROUPS_ORG_UNIT,
                'objectClass' => ['top', 'organizationalUnit'],
            ]);
        }

        $groupRn = ['cn' => $this->parentHelper->getParentGroups([], $group), 'ou' => array_merge([static::GROUPS_ORG_UNIT])];

        // TODO: Use ldap_add with LDAP_CONTROL_SYNC for PHP 7.3
        $groupSearchCount = $this->client->count(
            $this->client->search(sprintf('(cn=%s)', $group->getName()), $groupRn)
        );

        $this->assertResult($groupSearchCount !== null, 'Error during search!');

        if ($groupSearchCount === 0) {
            $res = $this->client->add($groupInfo, $groupRn);
        } else {
            $res = $this->client->modify($groupInfo, $groupRn);
        }

        $this->assertResult((bool) $res, sprintf('Group \'%s\' wasn\'t added', $group->getName()));

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
            $groupInfo['uniqueMember'][] = $this->client->getDn(
                $this->mapper->map($member),
                ['ou' => LdapUserPushAdapter::USERS_ORG_UNIT]
            );
        }

        return $groupInfo;
    }
}
