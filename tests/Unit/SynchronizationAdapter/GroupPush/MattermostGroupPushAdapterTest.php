<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Tests\Unit\SynchronizationAdapter\GroupPush;

use BadMethodCallException;
use Gnello\Mattermost\Driver;
use Gnello\Mattermost\Models\TeamModel;
use Gnello\Mattermost\Models\UserModel;
use LinkORB\OrgSync\DTO\Group;
use LinkORB\OrgSync\DTO\User;
use LinkORB\OrgSync\SynchronizationAdapter\GroupPush\MattermostGroupPushAdapter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class MattermostGroupPushAdapterTest extends TestCase
{
    /** @var MattermostGroupPushAdapter */
    private $adapter;

    /** @var UserModel|MockObject */
    private $userModel;

    /** @var TeamModel|MockObject */
    private $teamModel;

    protected function setUp(): void
    {
        $this->userModel = $this->createMock(UserModel::class);
        $this->teamModel = $this->createMock(TeamModel::class);

        $this->adapter = new MattermostGroupPushAdapter(
            $this->createConfiguredMock(
                Driver::class, ['getTeamModel' => $this->teamModel, 'getUserModel' => $this->userModel]
            )
        );
    }

    public function testCreateGroupWithMembers()
    {
        $group = new Group(
            'grOne',
            'First',
            null,
            null,
            [new User('temp'), new User('test1'), new User('user')]
        );
        $id = '1444';

        $this->teamModel
            ->expects($this->once())
            ->method('getTeamByName')
            ->with($group->getName())
            ->willReturn(
                $this->createConfiguredMock(
                    ResponseInterface::class,
                    ['getStatusCode' => 200, 'getBody' => json_encode(['delete_at' => 1])]
                )
            );

        $this->teamModel
            ->expects($this->once())
            ->method('createTeam')
            ->with([
                'name' => $group->getName(),
                'display_name' => $group->getDisplayName(),
                'type' => 'I',
            ])
            ->willReturn(
                $this->createConfiguredMock(
                    ResponseInterface::class,
                    ['getStatusCode' => 200, 'getBody' => json_encode(['id' => $id])]
                )
            );

        $this->userModel
            ->expects($this->exactly(count($group->getMembers())))
            ->method('getUserByUsername')
            ->withConsecutive(...array_map(function (User $user) {
                return [$user->getUsername()];
            }, $group->getMembers()))
            ->willReturnCallback(function (string $username) {
                return $this->createConfiguredMock(
                    ResponseInterface::class,
                    ['getBody' => json_encode(['id' => $username . '_user'])]
                );
            });

        $this->teamModel
            ->expects($this->once())
            ->method('addMultipleUsers')
            ->with($id, array_map(function (User $user) use ($id) {
                return [
                    'team_id' => $id,
                    'user_id' => $user->getUsername() . '_user',
                ];
            }, $group->getMembers()));

        $this->assertSame($this->adapter, $this->adapter->pushGroup($group));
    }

    public function testUpdateGroup()
    {
        $group = new Group('grOne', 'First');
        $id = '1001';

        $this->teamModel
            ->method('getTeamByName')
            ->with($group->getName())
            ->willReturn(
                $this->createConfiguredMock(
                    ResponseInterface::class,
                    ['getStatusCode' => 200, 'getBody' => json_encode(['delete_at' => 0, 'id' => $id])]
                )
            );

        $this->teamModel
            ->expects($this->once())
            ->method('updateTeam')
            ->with(
                $id,
                ['id' => $id, 'display_name' => $group->getDisplayName()]
            )
            ->willReturn($this->createConfiguredMock(ResponseInterface::class, ['getStatusCode' => 200]));

        $this->assertSame($this->adapter, $this->adapter->pushGroup($group));
    }

    public function testPushGroupException()
    {
        $this->teamModel->method('getTeamByName')
            ->willReturn(
                $this->createConfiguredMock(
                    ResponseInterface::class,
                    ['getStatusCode' => 400]
                )
            );

        $this->teamModel
            ->method('createTeam')
            ->willReturn($this->createConfiguredMock(ResponseInterface::class, ['getStatusCode' => 400]));

        $this->expectException(BadMethodCallException::class);

        $this->adapter->pushGroup(new Group('', ''));
    }
}
