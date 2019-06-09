<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Tests\Unit\SynchronizationMediator;

use LinkORB\OrgSync\AdapterFactory\AdapterFactoryInterface;
use LinkORB\OrgSync\AdapterFactory\AdapterFactoryPoolInterface;
use LinkORB\OrgSync\AdapterFactory\GithubAdapterFactory;
use LinkORB\OrgSync\DTO\Group;
use LinkORB\OrgSync\DTO\Organization;
use LinkORB\OrgSync\DTO\User;
use LinkORB\OrgSync\SynchronizationAdapter\GroupPush\GroupPushInterface;
use LinkORB\OrgSync\SynchronizationAdapter\OrganizationPull\OrganizationPullInterface;
use LinkORB\OrgSync\SynchronizationAdapter\OrganizationPush\OrganizationPushInterface;
use LinkORB\OrgSync\SynchronizationAdapter\SetPassword\SetPasswordInterface;
use LinkORB\OrgSync\SynchronizationAdapter\UserPush\UserPushInterface;
use LinkORB\OrgSync\SynchronizationMediator\SynchronizationMediator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SynchronizationMediatorTest extends TestCase
{
    /** @var SynchronizationMediator */
    private $mediator;

    /** @var AdapterFactoryInterface|MockObject */
    private $adapterFactory;

    /** @var AdapterFactoryPoolInterface|MockObject */
    private $adapterFactoryPool;

    /** @var OrganizationPushInterface|MockObject */
    private $organizationPushAdapter;

    /** @var GroupPushInterface|MockObject */
    private $groupPushAdapter;

    /** @var UserPushInterface|MockObject */
    private $userPushAdapter;

    /** @var SetPasswordInterface|MockObject */
    private $setPasswordAdapter;

    /** @var OrganizationPullInterface|MockObject */
    private $organizationPullAdapter;

    protected function setUp(): void
    {
        $this->organizationPushAdapter = $this->createMock(OrganizationPushInterface::class);
        $this->groupPushAdapter = $this->createMock(GroupPushInterface::class);
        $this->userPushAdapter = $this->createMock(UserPushInterface::class);
        $this->setPasswordAdapter = $this->createMock(SetPasswordInterface::class);
        $this->organizationPullAdapter = $this->createMock(OrganizationPullInterface::class);

        $this->adapterFactory = $this->createConfiguredMock(AdapterFactoryInterface::class, [
            'createOrganizationPushAdapter' => $this->organizationPushAdapter,
            'createGroupPushAdapter' => $this->groupPushAdapter,
            'createUserPushAdapter' => $this->userPushAdapter,
            'createSetPasswordAdapter' => $this->setPasswordAdapter,
            'createOrganizationPullAdapter' => $this->organizationPullAdapter,
        ]);

        $this->adapterFactoryPool = $this->createMock(AdapterFactoryPoolInterface::class);
        $this->adapterFactoryPool->method('get')->willReturn($this->adapterFactory);

        $this->mediator = (new SynchronizationMediator($this->adapterFactoryPool))
            ->setAdapterFamily('test');

        parent::setUp();
    }

    public function testPushOrganization()
    {
        $organization = $this->createMock(Organization::class);

        $this->adapterFactory->expects($this->once())->method('createOrganizationPushAdapter');

        $this->organizationPushAdapter->expects($this->once())->method('push')->with($organization)->willReturnSelf();

        $this->assertSame($this->mediator, $this->mediator->pushOrganization($organization));
    }

    public function testPushGroup()
    {
        $group = $this->createMock(Group::class);

        $this->adapterFactory->expects($this->once())->method('createGroupPushAdapter');

        $this->groupPushAdapter->expects($this->once())->method('push')->with($group)->willReturnSelf();

        $this->assertSame($this->mediator, $this->mediator->pushGroup($group));
    }

    public function testPushUser()
    {
        $user = $this->createMock(User::class);

        $this->adapterFactory->expects($this->once())->method('createUserPushAdapter');

        $this->userPushAdapter->expects($this->once())->method('push')->with($user)->willReturnSelf();

        $this->assertSame($this->mediator, $this->mediator->pushUser($user));
    }

    public function testSetPassword()
    {
        $user = $this->createMock(User::class);
        $password = '1234Qwer';

        $this->adapterFactory->expects($this->once())->method('createSetPasswordAdapter');

        $this->setPasswordAdapter->expects($this->once())->method('set')->with($user, $password)->willReturnSelf();

        $this->assertSame($this->mediator, $this->mediator->setPassword($user, $password));
    }

    public function testPullOrganization()
    {
        $this->adapterFactory->expects($this->once())->method('createOrganizationPullAdapter');

        $organization = $this->createMock(Organization::class);

        $this->organizationPullAdapter->expects($this->once())->method('pull')->with()->willReturn($organization);

        $this->assertSame($organization, $this->mediator->pullOrganization());
    }

    public function testSetAdapterFamily()
    {
        $this->adapterFactoryPool
            ->expects($this->once())
            ->method('get')
            ->with(GithubAdapterFactory::ADAPTER_KEY)
            ->willReturnSelf();

        $this->assertSame(
            $this->mediator,
            $this->mediator->setAdapterFamily(GithubAdapterFactory::ADAPTER_KEY)
        );
    }
}
