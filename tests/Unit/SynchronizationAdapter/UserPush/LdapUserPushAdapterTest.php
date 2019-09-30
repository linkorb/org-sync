<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Tests\Unit\SynchronizationAdapter\UserPush;

use LinkORB\OrgSync\DTO\User;
use LinkORB\OrgSync\Services\Ldap\Client;
use LinkORB\OrgSync\Services\Ldap\UserDataMapper;
use LinkORB\OrgSync\SynchronizationAdapter\UserPush\LdapUserPushAdapter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

class LdapUserPushAdapterTest extends TestCase
{
    /** @var LdapUserPushAdapter */
    private $adapter;

    /** @var Client|MockObject */
    private $client;

    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
        $this->adapter = new LdapUserPushAdapter($this->client, new UserDataMapper());
    }

    /**
     * @dataProvider getTestPushUserData
     */
    public function testPushUser(User $userObj, array $userArr, array $searchResults)
    {
        $this->client
            ->expects($this->exactly(2))
            ->method('search')
            ->withConsecutive(
                [$this->anything()],
                [$this->callback(function (string $query) use ($userObj) {
                    return strstr($query, $userObj->getUsername()) !== false;
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
                ->with($userArr, $dn)
                ->willReturn(true);
        } else {
            $this->client
                ->expects($this->once())
                ->method('add')
                ->with($userArr, ['uid' => $userObj->getUsername(), 'ou' => LdapUserPushAdapter::USERS_ORG_UNIT])
                ->willReturn(true);
        }


        $this->assertSame($this->adapter, $this->adapter->pushUser($userObj));
    }

    public function testPushUserOrgUnitCreation()
    {
        $user = new User('test');

        $this->client->method('add')->willReturn(true);
        $this->client->method('modify')->willReturn(true);

        $this->client
            ->expects($this->exactly(2))
            ->method('search')
            ->withConsecutive([sprintf('(ou=%s)', LdapUserPushAdapter::USERS_ORG_UNIT)], [$this->anything()])
            ->willReturnOnConsecutiveCalls([], [1]);

        $this->client->expects($this->once())->method('count')->willReturn(0);

        $this->client->expects($this->atLeastOnce())->method('add')->withConsecutive(
            [
                [
                    'ou' => LdapUserPushAdapter::USERS_ORG_UNIT,
                    'objectClass' => ['top', 'organizationalUnit'],
                ],
                ['ou' => LdapUserPushAdapter::USERS_ORG_UNIT]
            ],
            [$this->anything()]
        );

        $this->assertSame($this->adapter, $this->adapter->pushUser($user));
    }

    public function testPushUserSearchException()
    {
        $this->client->method('count')->willReturn(null);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Error during search!');

        $this->adapter->pushUser(new User('', null, ''));
    }

    public function testPushUserAddException()
    {
        $this->client->method('count')->willReturn(1);
        $this->client->method('add')->willReturn(false);
        $this->client->method('first')->willReturn(false);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('User \'undefined\' wasn\'t pushed');

        $this->adapter->pushUser(new User('undefined', null, ''));
    }

    public function getTestPushUserData(): array
    {
        return [
            [
                new User('temp123', null, 'temp@c.com', 'star', null, ['lastName' => 'Testenko']),
                [
                    'cn' => 'temp123',
                    'uid' => 'temp123',
                    'sn' => 'Testenko',
                    'mail' => 'temp@c.com',
                    'displayName' => 'star',
                    'objectClass' => ['inetOrgPerson', 'organizationalPerson', 'person', 'top'],
                ],
                [],
            ],
            [
                new User('temp000', null, 'temp99@a.com', 'super mega star', 'ava.gif'),
                [
                    'cn' => 'temp000',
                    'uid' => 'temp000',
                    'sn' => 'star',
                    'mail' => 'temp99@a.com',
                    'displayName' => 'super mega star',
                    'Photo' => 'ava.gif',
                    'objectClass' => ['inetOrgPerson', 'organizationalPerson', 'person', 'top'],
                ],
                [1],
            ],
            [
                new User('987', null, 'a@a.nl'),
                [
                    'cn' => '987',
                    'uid' => '987',
                    'sn' => '',
                    'mail' => 'a@a.nl',
                    'objectClass' => ['inetOrgPerson', 'organizationalPerson', 'person', 'top'],
                ],
                [1, 2, 3]
            ]
        ];
    }
}
