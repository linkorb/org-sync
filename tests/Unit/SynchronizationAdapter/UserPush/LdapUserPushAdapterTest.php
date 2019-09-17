<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Tests\Unit\SynchronizationAdapter\UserPush;

use LinkORB\OrgSync\DTO\User;
use LinkORB\OrgSync\Services\Ldap\Client;
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
        $this->adapter = new LdapUserPushAdapter($this->client);
    }

    /**
     * @dataProvider getTestPushUserData
     */
    public function testPushUser(User $userObj, array $userArr, array $searchResults)
    {
        $this->client
            ->expects($this->once())
            ->method('search')
            ->with($this->callback(function (string $query) use ($userObj) {
                return strstr($query, $userObj->getUsername()) !== false;
            }))
            ->willReturn($searchResults);

        $this->client
            ->expects($this->once())
            ->method('count')
            ->with($searchResults)
            ->willReturnCallback(function (array $result) {
                return count($result);
            });

        $this->client
            ->expects($this->once())
            ->method(count($searchResults) > 0 ? 'modify' : 'add')
            ->with($userArr)
            ->willReturn(true);

        $this->assertSame($this->adapter, $this->adapter->pushUser($userObj));
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
        $this->client->method('count')->willReturn(0);
        $this->client->method('add')->willReturn(false);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('User \'undefined\' wasn\'t added');

        $this->adapter->pushUser(new User('undefined', null, ''));
    }

    public function getTestPushUserData(): array
    {
        return [
            [
                new User('temp123', null, 'temp@c.com', 'star', null, ['lastName' => 'Testenko']),
                [
                    'cn' => 'temp123',
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
                    'sn' => '',
                    'mail' => 'a@a.nl',
                    'objectClass' => ['inetOrgPerson', 'organizationalPerson', 'person', 'top'],
                ],
                [1,2,3]
            ]
        ];
    }
}
