<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Tests\Unit\SynchronizationMediator;

use LinkORB\OrgSync\DTO\Target\Camunda;
use LinkORB\OrgSync\Services\InputHandler;
use LinkORB\OrgSync\SynchronizationAdapter\AdapterFactory\AdapterFactoryInterface;
use LinkORB\OrgSync\SynchronizationAdapter\AdapterFactory\AdapterFactoryPoolInterface;
use LinkORB\OrgSync\SynchronizationAdapter\AdapterFactory\GithubAdapterFactory;
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

    /** @var InputHandler|MockObject */
    private $inputHandler;

    protected function setUp(): void
    {
        $this->inputHandler = $this->createMock(InputHandler::class);
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

        $this->mediator = (new SynchronizationMediator($this->adapterFactoryPool, $this->inputHandler));

        parent::setUp();
    }

    public function testInitialize()
    {
        $targets = ['', 12];
        $organizations = [1, 'qwer', 3];

        $this->inputHandler
            ->expects($this->once())
            ->method('handle')
            ->with($targets, $organizations)
            ->willReturn($this->createMock(Organization::class));

        $this->mediator->initialize($targets, $organizations);
    }

    public function testPushOrganization()
    {
        $this->mediator = $this->createPartialMock(SynchronizationMediator::class, ['pushUser', 'pushGroup']);
        $this->mediator->__construct($this->adapterFactoryPool, $this->inputHandler);

        $targets = [
            new Camunda(null, null, 'test', 'temp'),
            new Camunda('test', 'user', 'local', 'adapter'),
        ];

        $this->inputHandler->expects($this->once())->method('getTargets')->willReturn($targets);
        $users = [$this->createMock(User::class), $this->createMock(User::class), $this->createMock(User::class)];
        $groups = [$this->createConfiguredMock(Group::class, ['getTargets' => $targets])];
        $organization = $this->createConfiguredMock(Organization::class, [
            'getUsers' => $users,
            'getGroups' => $groups,
        ]);

        $targetsConsecutive = array_map(function (Camunda $target) {
            return [$target];
        }, $targets);

        foreach ($groups as $group) {
            $targetsConsecutive = array_merge($targetsConsecutive, $targetsConsecutive);
        }

        $this->adapterFactoryPool
            ->expects($this->exactly(count($targets) * (count($groups) + 1)))
            ->method('get')
            ->withConsecutive(...$targetsConsecutive);

        $this->mediator->expects($this->exactly(count($targets) * count($users)))->method('pushUser');
        foreach ($users as $index => $user) {
            $this->mediator->expects($this->at($index))->method('pushUser')->with($user);
        }

        $groupsConsecutive = array_reduce($groups, function (array $result, Group $group) use ($targets) {
            return array_merge($result, array_fill(0, count($targets), [$group]));
        }, []);
        $this->mediator
            ->expects($this->exactly(count($targets) * count($groups)))
            ->method('pushGroup')
            ->withConsecutive(...$groupsConsecutive);

        $this->adapterFactory->expects($this->exactly(count($targets)))->method('createOrganizationPushAdapter');

        $this->organizationPushAdapter
            ->expects($this->exactly(count($targets)))
            ->method('pushOrganization')
            ->with($organization)->willReturnSelf();

        $this->assertSame($this->mediator, $this->mediator->pushOrganization($organization));
    }

    public function testPushGroup()
    {
        $group = $this->createMock(Group::class);

        $this->adapterFactory->expects($this->once())->method('createGroupPushAdapter');

        $this->groupPushAdapter->expects($this->once())->method('pushGroup')->with($group)->willReturnSelf();

        $this->assertSame(
            $this->mediator,
            $this->mediator->setTarget($this->createMock(Camunda::class))->pushGroup($group)
        );
    }

    public function testPushUser()
    {
        $user = $this->createMock(User::class);

        $this->adapterFactory->expects($this->once())->method('createUserPushAdapter');

        $this->userPushAdapter->expects($this->once())->method('pushUser')->with($user)->willReturnSelf();

        $this->assertSame(
            $this->mediator,
            $this->mediator->setTarget($this->createMock(Camunda::class))->pushUser($user)
        );
    }

    public function testSetPassword()
    {
        $user = $this->createMock(User::class);
        $password = '1234Qwer';

        $this->adapterFactory->expects($this->once())->method('createSetPasswordAdapter');

        $this->setPasswordAdapter->expects($this->once())->method('setPassword')->with($user, $password)->willReturnSelf();

        $this->assertSame(
            $this->mediator,
            $this->mediator->setTarget($this->createMock(Camunda::class))->setPassword($user, $password)
        );
    }

    public function testPullOrganization()
    {
        $this->adapterFactory->expects($this->once())->method('createOrganizationPullAdapter');

        $organization = $this->createMock(Organization::class);

        $this->organizationPullAdapter->expects($this->once())->method('pullOrganization')->with()->willReturn($organization);

        $this->assertSame(
            $organization,
            $this->mediator->setTarget($this->createMock(Camunda::class))->pullOrganization()
        );
    }
}
