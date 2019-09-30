<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Tests\Unit\Services\SyncRemover;

use LinkORB\OrgSync\DTO\Group;
use LinkORB\OrgSync\DTO\Organization;
use LinkORB\OrgSync\DTO\User;
use LinkORB\OrgSync\Services\Ldap\Client;
use LinkORB\OrgSync\Services\Ldap\LdapParentHelper;
use LinkORB\OrgSync\Services\SyncRemover\LdapSyncRemover;
use LinkORB\OrgSync\SynchronizationAdapter\GroupPush\LdapGroupPushAdapter;
use LinkORB\OrgSync\SynchronizationAdapter\UserPush\LdapUserPushAdapter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LdapSyncRemoverTest extends TestCase
{
    /** @var LdapSyncRemover */
    private $syncRemover;

    /** @var Client|MockObject */
    private $client;

    /** @var LdapParentHelper|MockObject */
    private $parentHelper;

    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
        $this->parentHelper = $this->createMock(LdapParentHelper::class);
        $this->syncRemover = new LdapSyncRemover($this->client, $this->parentHelper);
    }

    /**
     * @dataProvider getRemoveUsersData
     */
    public function testRemoveNonExistsUsers(array $orgUsers, array $existingUsers, array $expectedToRemoveUsers)
    {
        $organization = new Organization('', $orgUsers);

        $searchResult = new \stdClass();
        $this->client
            ->expects($this->exactly(3))
            ->method('search')
            ->withConsecutive(
                [sprintf('(ou=%s)', LdapUserPushAdapter::USERS_ORG_UNIT)],
                [sprintf('(ou=%s)', LdapGroupPushAdapter::GROUPS_ORG_UNIT)],
                ['(objectClass=inetOrgPerson)']
            )
            ->willReturnOnConsecutiveCalls(true, null, $searchResult);
        $this->client
            ->expects($this->exactly(2))
            ->method('count')
            ->withConsecutive([true], [null])
            ->willReturnOnConsecutiveCalls(1, 0);

        $this->client
            ->expects($this->once())
            ->method('all')
            ->with($searchResult)
            ->willReturn($existingUsers);

        $this->client
            ->expects($this->exactly(count($expectedToRemoveUsers)))
            ->method('remove')
            ->withConsecutive(...$expectedToRemoveUsers);

        $this->assertNull($this->syncRemover->removeNonExists($organization));
    }

    /**
     * @param Group[] $orgGroups
     * @dataProvider getRemoveGroupsData
     */
    public function testRemoveNonExistsGroups(array $orgGroups, array $existingGroups, array $expectedToRemoveGroups)
    {
        $organization = new Organization('', [], $orgGroups);

        $searchResult = new \stdClass();
        $this->client
            ->expects($this->exactly(3))
            ->method('search')
            ->withConsecutive(
                [sprintf('(ou=%s)', LdapUserPushAdapter::USERS_ORG_UNIT)],
                [sprintf('(ou=%s)', LdapGroupPushAdapter::GROUPS_ORG_UNIT)],
                ['(objectClass=groupOfUniqueNames)', ['ou' => LdapGroupPushAdapter::GROUPS_ORG_UNIT]]
            )
            ->willReturnOnConsecutiveCalls(null, true, $searchResult);
        $this->client
            ->expects($this->exactly(2))
            ->method('count')
            ->withConsecutive([null], [true])
            ->willReturnOnConsecutiveCalls(0, 1);

        $this->client
            ->expects($this->once())
            ->method('all')
            ->with($searchResult)
            ->willReturn($existingGroups);

        $this->parentHelper
            ->method('getParentGroups')
            ->willReturnCallback(function (array $initialParent, Group $group) {
                return [$group->getName()];
            });

        $this->client
            ->method('generateDn')
            ->willReturnCallback(function (array $rdn) use ($orgGroups) {
                foreach ($orgGroups as $group) {
                    if ($group->getName() === $rdn['cn'][0]) {
                        return $group->getProperties()['dn'];
                    }
                }

                return '';
            });

        $this->client
            ->expects($this->exactly(count($expectedToRemoveGroups)))
            ->method('remove')
            ->withConsecutive(...$expectedToRemoveGroups);

        $this->assertNull($this->syncRemover->removeNonExists($organization));
    }

    public function getRemoveUsersData(): array
    {
        return [
            [
                [
                    new User('test11'),
                    new User('test33'),
                ],
                [
                    'count' => 3,
                    ['cn' => ['test011', 'test11'], 'dn' => 'test11del422'],
                    ['cn' => ['test11'], 'dn' => 'test11del22'],
                    ['cn' => ['test123'], 'dn' => 'test11del99'],
                ],
                [['test11del422'], ['test11del99']],
            ],
            [
                [],
                [
                    'count' => 2,
                    ['cn' => ['test11'], 'dn' => 'test11del22'],
                    ['cn' => ['test123'], 'dn' => 'test11del99'],
                ],
                [['test11del22'], ['test11del99']],
            ],
            [
                [
                    new User('test11'),
                    new User('test33'),
                ],
                [
                    'count' => 0,
                ],
                [],
            ],
            [
                [
                    new User('test11'),
                    new User('test33'),
                ],
                [
                    'count' => 2,
                    ['cn' => ['test11'], 'dn' => 'test11del22'],
                    ['cn' => ['test33'], 'dn' => 'test11del99'],
                ],
                [],
            ],
        ];
    }

    public function getRemoveGroupsData(): array
    {
        return [
            [
                [
                    new Group('test011', '', null, null, [], ['dn' => 'test11del22']),
                    new Group('test033', '', null, null, [], ['dn' => 'test91del62']),
                ],
                [
                    'count' => 3,
                    ['cn' => ['test0011', 'test011'], 'dn' => 'test11del422'],
                    ['cn' => ['test011'], 'dn' => 'test11del22'],
                    ['cn' => ['test123'], 'dn' => 'test11del99'],
                ],
                [['test11del422'], ['test11del99']],
            ],
            [
                [],
                [
                    'count' => 2,
                    ['cn' => ['test11'], 'dn' => 'test121del22'],
                    ['cn' => ['test123'], 'dn' => 'test121del99'],
                ],
                [['test121del22'], ['test121del99']],
            ],
            [
                [
                    new Group('test011', '', null, null, [], ['dn' => 'rty']),
                    new Group('test033', '', null, null, [], ['dn' => 'qwe']),
                ],
                [
                    'count' => 0,
                ],
                [],
            ],
            [
                [
                    new Group('group11', '', null, null, [], ['dn' => '1rty']),
                    new Group('group33', '', null, null, [], ['dn' => 'rty3']),
                ],
                [
                    'count' => 2,
                    ['cn' => ['group11'], 'dn' => '1rty'],
                    ['cn' => ['group33'], 'dn' => 'rty3'],
                ],
                [],
            ],
            [
                [
                    new Group('group11', '', null, null, [], ['dn' => 'different']),
                    new Group('group33', '', null, null, [], ['dn' => 'rty3']),
                ],
                [
                    'count' => 2,
                    ['cn' => ['group11'], 'dn' => '1rty'],
                    ['cn' => ['group33'], 'dn' => 'rty3'],
                ],
                [['1rty']],
            ],
        ];
    }
}
