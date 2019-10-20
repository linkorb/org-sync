<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Tests\Unit\SynchronizationAdapter\SetPassword;

use LinkORB\OrgSync\DTO\User;
use LinkORB\OrgSync\Services\Ldap\Client;
use LinkORB\OrgSync\SynchronizationAdapter\SetPassword\LdapSetPasswordAdapter;
use LinkORB\OrgSync\SynchronizationAdapter\UserPush\LdapUserPushAdapter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

class LdapSetPasswordAdapterTest extends TestCase
{
    /**
     * @var LdapSetPasswordAdapter|MockObject
     */
    private $adapter;

    /**
     * @var Client|MockObject
     */
    private $client;

    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
        $this->adapter = $this->createPartialMock(LdapSetPasswordAdapter::class, ['encodePassword']);
        $this->adapter->__construct($this->client);
    }

    public function testSetPassword()
    {
        $user = new User('passTest', 'p@ssword');
        $dn = 'qwertyuio';
        $searchResult = [['dn' => $dn, 'found' => true]];
        $pass = '{SSHA}test123';

        $this->client
            ->expects($this->exactly(2))
            ->method('search')
            ->withConsecutive(
                ['(ou=' . LdapUserPushAdapter::USERS_ORG_UNIT . ')'],
                ['(uid=' . $user->getUsername() . ')', ['ou' => LdapUserPushAdapter::USERS_ORG_UNIT]]
            )
            ->willReturnOnConsecutiveCalls([1], $searchResult);

        $this->client
            ->expects($this->once())
            ->method('count')
            ->willReturnCallback(function (array $arg) {
                return count($arg);
            });
        $this->client
            ->expects($this->once())
            ->method('first')
            ->with($searchResult)
            ->willReturnCallback(function (array $arg) {
                return reset($arg);
            });
        $this->client
            ->expects($this->once())
            ->method('getDn')
            ->with(reset($searchResult))
            ->willReturnCallback(function (array $arg) {
                return $arg['dn'];
            });

        $this->adapter->expects($this->once())->method('encodePassword')->with('p@ssword')->willReturn($pass);

        $this->client
            ->expects($this->once())
            ->method('modify')
            ->with(['userPassword' => $pass], $dn)
            ->willReturn(true);

        $this->assertSame($this->adapter, $this->adapter->setPassword($user));
    }

    public function testSetPasswordOuException()
    {
        $this->expectExceptionMessage('Error during search!');
        $this->expectException(UnexpectedValueException::class);

        $this->adapter->setPassword(new User(''));
    }

    public function testSetPasswordException()
    {
        $this->client->method('count')->willReturn(1);
        $this->client->method('getDn')->willReturn('');

        $this->expectExceptionMessage('User \'\' password wasn\'t changed');
        $this->expectException(UnexpectedValueException::class);

        $this->adapter->setPassword(new User('', ''));
    }
}
