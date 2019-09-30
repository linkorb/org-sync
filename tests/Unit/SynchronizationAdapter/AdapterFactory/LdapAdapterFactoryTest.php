<?php

namespace LinkORB\OrgSync\Tests\Unit\SynchronizationAdapter\AdapterFactory;

use LinkORB\OrgSync\DTO\Target;
use LinkORB\OrgSync\DTO\Target\Ldap;
use LinkORB\OrgSync\Services\Ldap\Client;
use LinkORB\OrgSync\Services\SyncRemover\LdapSyncRemover;
use LinkORB\OrgSync\SynchronizationAdapter\AdapterFactory\LdapAdapterFactory;
use LinkORB\OrgSync\SynchronizationAdapter\UserPush\LdapUserPushAdapter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LdapAdapterFactoryTest extends TestCase
{
    /** @var LdapAdapterFactory|MockObject */
    private $factory;

    /** @var Client|MockObject */
    private $client;

    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
        $this->client->method('bind')->willReturnSelf();
        $this->client->method('init')->willReturnSelf();
        $this->factory = $this->createPartialMock(LdapAdapterFactory::class, ['getClient']);
        $this->factory->method('getClient')->willReturn($this->client);
        $this->factory->setTarget(new Ldap('', '', '', '', []));
        $this->factory->__construct();
    }

    public function testCreateUserPushAdapter()
    {
        $this->assertInstanceOf(LdapUserPushAdapter::class, $this->factory->createUserPushAdapter());
    }

    /**
     * @dataProvider getSupportsData
     */
    public function testSupports(string $action, bool $isSupported)
    {
        $this->assertEquals($isSupported, $this->factory->supports($action));
    }

    public function testCreateSyncRemover()
    {
        $this->assertInstanceOf(LdapSyncRemover::class, $this->factory->createSyncRemover());
    }

    public function testSetTarget()
    {
        $this->client->expects($this->once())->method('bind');
        $this->client->expects($this->once())->method('init');

        $this->assertSame($this->factory, $this->factory->setTarget(new Ldap('', '', '', '', [])));
    }

    public function getSupportsData(): array
    {
        return [
            [Target::GROUP_PUSH, true],
            [Target::SET_PASSWORD, true],
            [Target::USER_PUSH, true],
            [Target::PULL_ORGANIZATION, false],
            ['', false],
        ];
    }
}
