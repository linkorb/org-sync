<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Tests\Unit\Services\SyncRemover;

use Gnello\Mattermost\Driver;
use Gnello\Mattermost\Models\TeamModel;
use Gnello\Mattermost\Models\UserModel;
use LinkORB\OrgSync\DTO\Group;
use LinkORB\OrgSync\DTO\Organization;
use LinkORB\OrgSync\DTO\User;
use LinkORB\OrgSync\Services\Mattermost\BaseEntriesProvider;
use LinkORB\OrgSync\Services\SyncRemover\MattermostSyncRemover;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MattermostSyncRemoverTest extends TestCase
{
    /** @var MattermostSyncRemover */
    private $remover;

    /** @var TeamModel|MockObject */
    private $teamModel;

    /** @var UserModel|MockObject */
    private $userModel;

    /** @var BaseEntriesProvider|MockObject */
    private $provider;

    protected function setUp(): void
    {
        $this->teamModel = $this->createMock(TeamModel::class);
        $this->userModel = $this->createMock(UserModel::class);
        $this->provider = $this->createMock(BaseEntriesProvider::class);

        $this->remover = new MattermostSyncRemover(
            $this->createConfiguredMock(
                Driver::class,
                ['getTeamModel' => $this->teamModel, 'getUserModel' => $this->userModel]
            ),
            $this->provider
        );
    }

    /**
     * @dataProvider getRemoveDataProvider
     */
    public function testRemoveNonExists(
        array $existingUsers,
        array $existingGroups,
        array $existingMembers,
        Organization $organization,
        array $expectedRemoveUserIds,
        array $expectedRemoveGroupIds,
        array $expectedRemoveMembers
    )
    {
        $this->provider->expects($this->once())->method('getExistingUsers')->willReturn($existingUsers);
        $this->provider->expects($this->once())->method('getExistingGroups')->willReturn($existingGroups);
        $this->provider
            ->expects($this->exactly(count($existingMembers)))
            ->method('getTeamMembers')
            ->withConsecutive(...array_map(function (string $key) {
                return [$key];
            }, array_keys($existingMembers)))
            ->willReturnOnConsecutiveCalls(...array_values($existingMembers));

        $this->userModel
            ->expects($this->exactly(count($expectedRemoveUserIds)))
            ->method('deactivateUserAccount')
            ->withConsecutive(...array_map(function (string $id) {
                return [$id];
            }, $expectedRemoveUserIds));

        $this->teamModel
            ->expects($this->exactly(count($expectedRemoveGroupIds)))
            ->method('deleteTeam')
            ->withConsecutive(...array_map(function (string $id) {
                return [$id];
            }, $expectedRemoveGroupIds));

        $this->teamModel
            ->expects($this->exactly(count($expectedRemoveMembers)))
            ->method('removeUser')
            ->withConsecutive(...$expectedRemoveMembers);

        $this->assertNull($this->remover->removeNonExists($organization));
    }

    public function getRemoveDataProvider(): array
    {
        return [
            [
                [
                    [
                        'username' => 'test11',
                        'id' => 'trhl56',
                    ],
                    [
                        'username' => 'user56',
                        'id' => 'khkjsf4',
                    ],
                    [
                        'username' => 'admin',
                        'id' => '1',
                    ],
                ],
                [
                    [
                        'name' => 'first',
                        'id' => '24rb',
                    ],
                    [
                        'name' => 'secret',
                        'id' => '658jhg',
                    ],
                    [
                        'name' => 'qwer',
                        'id' => 'gnt6',
                    ],
                    [
                        'name' => 'rty',
                        'id' => 'thyj8',
                    ],
                    [
                        'name' => 'uio',
                        'id' => 'hdtr56',
                    ],
                ],
                [
                    '24rb' => ['trhl56',],
                    'gnt6' => [],
                    'thyj8' => ['1'],
                ],
                new Organization(
                    'test',
                    [
                        new User('test11'),
                        new User('user567'),
                        new User('temp'),
                        new User('admin')
                    ],
                    [
                        new Group('first', '', null, null, [new User('test11')]),
                        new Group('qwerty', '', null, null, [new User('test11'), new User('user567')]),
                        new Group('secretqwer', '', null, null, [new User('admin'), new User('qw')]),
                        new Group('qwer', '', null, null, []),
                        new Group('rty', '', null, null, []),
                    ]
                ),
                ['khkjsf4'],
                ['658jhg', 'hdtr56'],
                [['thyj8', '1', []]]
            ]
        ];
    }
}
