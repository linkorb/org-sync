<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Tests\Unit\SynchronizationAdapter\GroupPush;

use LinkORB\OrgSync\DTO\Group;
use LinkORB\OrgSync\DTO\User;
use LinkORB\OrgSync\Services\Ldap\Client;
use LinkORB\OrgSync\Services\Ldap\LdapParentHelper;
use LinkORB\OrgSync\SynchronizationAdapter\GroupPush\LdapGroupPushAdapter;
use LinkORB\OrgSync\SynchronizationAdapter\UserPush\LdapUserPushAdapter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

class LdapGroupPushAdapterTest extends TestCase
{
    /**
     * @var LdapGroupPushAdapter
     */
    private $adapter;

    /**
     * @var Client|MockObject
     */
    private $client;

    /**
     * @var LdapParentHelper|MockObject
     */
    private $parentHelper;

    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
        $this->parentHelper = $this->createMock(LdapParentHelper::class);

        $this->adapter = new LdapGroupPushAdapter($this->client, $this->parentHelper);
    }

    /**
     * @dataProvider getTestPushGroupData
     */
    public function testPushGroup(Group $groupObj, array $groupArr, array $searchResults)
    {
        $parentGroups = ['some', 'parents'];
        $this->parentHelper->expects($this->once())->method('getParentGroups')->willReturn($parentGroups);
        $this->client
            ->method('generateDn')
            ->willReturnCallback(function (array $params) {
                return json_encode($params);
            });

        $this->client
            ->expects($this->exactly(2))
            ->method('search')
            ->withConsecutive(
                [$this->anything()],
                [$this->callback(function (string $query) use ($groupObj) {
                    return strstr($query, $groupObj->getName()) !== false;
                })]
            )
            ->willReturnOnConsecutiveCalls(null, $searchResults);

        $this->client->expects($this->once())->method('count')->willReturn(1);

        $this->client
            ->expects($this->once())
            ->method('first')
            ->with($searchResults)
            ->willReturnCallback(function (array $result) {
                return reset($result);
            });

        if (count($searchResults) > 0) {
            $dn = 'testDn';
            $this->client->expects($this->once())->method('getDn')->with(reset($searchResults))->willReturn($dn);

            $this->client
                ->expects($this->once())
                ->method('modify')
                ->with($groupArr, $dn)
                ->willReturn(true);
        } else {
            $this->client
                ->expects($this->once())
                ->method('add')
                ->with(
                    $groupArr,
                    [
                        'cn' => array_merge([$groupObj->getName()], $parentGroups),
                        'ou' => [LdapGroupPushAdapter::GROUPS_ORG_UNIT]
                    ]
                )
                ->willReturn(true);
        }

        $this->assertSame($this->adapter, $this->adapter->pushGroup($groupObj));
    }

    public function testPushUserOrgUnitCreation()
    {
        $group = new Group('test', '');

        $this->client->method('add')->willReturn(true);
        $this->client->method('modify')->willReturn(true);

        $this->client
            ->expects($this->exactly(2))
            ->method('search')
            ->withConsecutive([sprintf('(ou=%s)', LdapGroupPushAdapter::GROUPS_ORG_UNIT)], [$this->anything()])
            ->willReturnOnConsecutiveCalls([], [1]);

        $this->client->expects($this->once())->method('count')->willReturn(0);

        $this->client->expects($this->atLeastOnce())->method('add')->withConsecutive(
            [
                [
                    'ou' => LdapGroupPushAdapter::GROUPS_ORG_UNIT,
                    'objectClass' => ['top', 'organizationalUnit'],
                ],
                ['ou' => LdapGroupPushAdapter::GROUPS_ORG_UNIT]
            ],
            [$this->anything()]
        );

        $this->assertSame($this->adapter, $this->adapter->pushGroup($group));
    }

    public function testPushGroupSearchException()
    {
        $this->client->method('count')->willReturn(null);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Error during search!');

        $this->adapter->pushGroup(new Group('', ''));
    }

    public function testPushUserAddException()
    {
        $this->client->method('count')->willReturn(1);
        $this->client->method('add')->willReturn(false);
        $this->client->method('first')->willReturn(false);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Group \'undefined\' wasn\'t added');

        $this->adapter->pushGroup(new Group('undefined', ''));
    }

    public function getTestPushGroupData(): array
    {
        return [
            [
                new Group('temp123', '', null, null, [new User('temp1'), new User('temp2')]),
                [
                    'cn' => 'temp123',
                    'objectClass' => ['top', 'groupOfUniqueNames'],
                    'uniqueMember' => [
                        json_encode(['uid' => 'temp1', 'ou' => LdapUserPushAdapter::USERS_ORG_UNIT]),
                        json_encode(['uid' => 'temp2', 'ou' => LdapUserPushAdapter::USERS_ORG_UNIT]),
                    ],
                ],
                [],
            ],
            [
                new Group('temp000', ''),
                [
                    'cn' => 'temp000',
                    'objectClass' => ['top', 'groupOfUniqueNames'],
                    'uniqueMember' => [],
                ],
                [1],
            ],
            [
                new Group('987', ''),
                [
                    'cn' => '987',
                    'objectClass' => ['top', 'groupOfUniqueNames'],
                    'uniqueMember' => [],
                ],
                [1, 2, 3]
            ]
        ];
    }
}
