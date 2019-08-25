<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Tests\Unit\SynchronizationMediator;

use LinkORB\OrgSync\DTO\Target;
use LinkORB\OrgSync\DTO\Target\Camunda;
use LinkORB\OrgSync\Exception\SyncTargetException;
use LinkORB\OrgSync\Services\InputHandler;
use LinkORB\OrgSync\Services\SyncRemover\SyncRemoverInterface;
use LinkORB\OrgSync\SynchronizationAdapter\AdapterFactory\AdapterFactoryInterface;
use LinkORB\OrgSync\SynchronizationAdapter\AdapterFactory\AdapterFactoryPoolInterface;
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

    /** @var GroupPushInterface|MockObject */
    private $groupPushAdapter;

    /** @var UserPushInterface|MockObject */
    private $userPushAdapter;

    /** @var SetPasswordInterface|MockObject */
    private $setPasswordAdapter;

    /** @var OrganizationPullInterface|MockObject */
    private $organizationPullAdapter;

    /** @var SyncRemoverInterface|MockObject */
    private $syncRemover;

    /** @var InputHandler|MockObject */
    private $inputHandler;

    protected function setUp(): void
    {
        $this->inputHandler = $this->createMock(InputHandler::class);
        $this->groupPushAdapter = $this->createMock(GroupPushInterface::class);
        $this->userPushAdapter = $this->createMock(UserPushInterface::class);
        $this->setPasswordAdapter = $this->createMock(SetPasswordInterface::class);
        $this->organizationPullAdapter = $this->createMock(OrganizationPullInterface::class);
        $this->syncRemover = $this->createMock(SyncRemoverInterface::class);

        $this->adapterFactory = $this->createConfiguredMock(AdapterFactoryInterface::class, [
            'createGroupPushAdapter' => $this->groupPushAdapter,
            'createUserPushAdapter' => $this->userPushAdapter,
            'createSetPasswordAdapter' => $this->setPasswordAdapter,
            'createOrganizationPullAdapter' => $this->organizationPullAdapter,
            'createSyncRemover' => $this->syncRemover,
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
            new Camunda('test', 'temp'),
            new Camunda('local', 'adapter', 'test', 'user'),
        ];

        $this->inputHandler->expects($this->exactly(2))->method('getTargets')->willReturn($targets);
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
            $targetsConsecutive = array_merge($targetsConsecutive, $targetsConsecutive, $targetsConsecutive);
        }

        $this->adapterFactoryPool
            ->expects($this->exactly(count($targets) * (count($groups) + 2)))
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

        $this->adapterFactory->expects($this->exactly(count($targets)))->method('createSyncRemover');

        $this->syncRemover
            ->expects($this->exactly(count($targets)))
            ->method('removeNonExists')
            ->with($organization);

        $this->assertSame($this->mediator, $this->mediator->pushOrganization($organization));
    }

    public function testOrganizationPushNotSupported()
    {
        $targets = [
            new Camunda('', ''),
            new Camunda('', ''),
        ];

        $this->inputHandler->method('getTargets')->willReturn($targets);

        $users = [$this->createMock(User::class), $this->createMock(User::class), $this->createMock(User::class)];
        $groups = [$this->createConfiguredMock(Group::class, ['getTargets' => $targets])];

        $this->adapterFactory->method('supports')->willReturn(false);
        $this->adapterFactory->expects($this->never())->method('createUserPushAdapter');
        $this->adapterFactory->expects($this->never())->method('createGroupPushAdapter');

        $this->mediator->pushOrganization($this->createConfiguredMock(Organization::class, [
            'getUsers' => $users,
            'getGroups' => $groups,
        ]));
    }

    public function testPushGroup()
    {
        $group = $this->createMock(Group::class);

        $this->adapterFactory->expects($this->once())->method('createGroupPushAdapter');
        $this->adapterFactory->expects($this->once())->method('supports')->with(Target::GROUP_PUSH)->willReturn(true);

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
        $this->adapterFactory->expects($this->once())->method('supports')->with(Target::USER_PUSH)->willReturn(true);

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

        $targets = [
            $this->createMock(Camunda::class),
            $this->createMock(Camunda::class),
            $this->createMock(Camunda::class),
            $this->createMock(Camunda::class),
        ];

        $targetSupports = array_map(function (){return (bool) random_int(0, 1);}, $targets);

        $expectedTargets = [];
        foreach ($targets as $key => $target) {
            if ($targetSupports[$key]) {
                $expectedTargets[] = $target;
            }
        }

        $this->inputHandler
            ->expects($this->once())
            ->method('getTargets')
            ->willReturn($targets);

        $this->adapterFactory->expects($this->exactly(count($expectedTargets)))->method('createSetPasswordAdapter');
        $this->adapterFactory
            ->expects($this->exactly(count($targetSupports)))
            ->method('supports')
            ->with(Target::SET_PASSWORD)
            ->willReturnOnConsecutiveCalls(...$targetSupports);

        $this->setPasswordAdapter
            ->expects($this->exactly(count($expectedTargets)))
            ->method('setPassword')
            ->with($user, $password)
            ->willReturnSelf();

        $this->assertSame(
            $this->mediator,
            $this->mediator->setPassword($user, $password)
        );
    }

    public function testPullOrganization()
    {
        $this->adapterFactory->expects($this->once())->method('createOrganizationPullAdapter');
        $this->adapterFactory
            ->expects($this->once())
            ->method('supports')
            ->with(Target::PULL_ORGANIZATION)
            ->willReturn(true);

        $organization = $this->createMock(Organization::class);

        $this->organizationPullAdapter->expects($this->once())->method('pullOrganization')->with()->willReturn($organization);

        $this->assertSame(
            $organization,
            $this->mediator->setTarget($this->createMock(Camunda::class))->pullOrganization()
        );
    }

    public function testPullNotSupported()
    {
        $this->adapterFactory
            ->expects($this->once())
            ->method('supports')
            ->with(Target::PULL_ORGANIZATION)
            ->willReturn(false);

        $this->expectException(SyncTargetException::class);

        $this->mediator->setTarget($this->createMock(Camunda::class))->pullOrganization();
    }
}
