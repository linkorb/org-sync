<?php

namespace LinkORB\OrgSync\Tests\Unit\Services\Mattermost;

use Gnello\Mattermost\Driver;
use Gnello\Mattermost\Models\TeamModel;
use Gnello\Mattermost\Models\UserModel;
use LinkORB\OrgSync\Services\Mattermost\BaseEntriesProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class BaseEntriesProviderTest extends TestCase
{
    /** @var BaseEntriesProvider */
    private $provider;

    /** @var Driver|MockObject */
    private $driver;

    protected function setUp(): void
    {
        $this->driver = $this->createMock(Driver::class);
        $this->provider = new BaseEntriesProvider($this->driver);
    }

    /**
     * @dataProvider getTestMembersData
     */
    public function testGetTeamMembers(array $serverResponse, array $expectedMembers)
    {
        $teamId = '11df4f4l';

        $teamModel = $this->createMock(TeamModel::class);
        $this->driver->method('getTeamModel')->willReturn($teamModel);

        $teamModel
            ->expects($this->exactly(1))
            ->method('getTeamMembers')
            ->with($teamId, ['per_page' => 200])
            ->willReturn(
                $this->createConfiguredMock(ResponseInterface::class, ['getBody' => json_encode($serverResponse)])
            );

        $this->assertEquals($expectedMembers, $this->provider->getTeamMembers($teamId));
    }

    /**
     * @dataProvider getTestUsersData
     */
    public function testGetExistingUsers(array $serverResponse, array $expectedUsers)
    {
        $userModel = $this->createMock(UserModel::class);
        $this->driver->method('getUserModel')->willReturn($userModel);

        $userModel
            ->expects($this->exactly(1))
            ->method('getUsers')
            ->with(['per_page' => 200])
            ->willReturn(
                $this->createConfiguredMock(ResponseInterface::class, ['getBody' => json_encode($serverResponse)])
            );

        $this->assertEquals($expectedUsers, $this->provider->getExistingUsers());
    }

    /**
     * @dataProvider getTestGroupsData
     */
    public function testGetExistingGroups(array $serverResponse, array $expectedGroups)
    {
        $teamModel = $this->createMock(TeamModel::class);
        $this->driver->method('getTeamModel')->willReturn($teamModel);

        $teamModel
            ->expects($this->exactly(1))
            ->method('getTeams')
            ->with(['per_page' => 200])
            ->willReturn(
                $this->createConfiguredMock(ResponseInterface::class, ['getBody' => json_encode($serverResponse)])
            );

        $this->assertEquals($expectedGroups, $this->provider->getExistingGroups());
    }

    public function getTestMembersData(): array
    {
        return [
            [
                [
                    ['user_id' => 'a12s3d', 'delete_at' => 0],
                    ['user_id' => '5g8jf4', 'delete_at' => 100],
                    ['user_id' => '5gtyy', 'delete_at' => 0],
                ],
                ['a12s3d', '5gtyy'],
            ],
            [
                [
                    ['user_id' => 'a12s3d', 'delete_at' => 400],
                    ['user_id' => '5g8jf4', 'delete_at' => 100],
                    ['user_id' => '5gtyy', 'delete_at' => 1000],
                ],
                [],
            ],
            [
                [],
                [],
            ],
        ];
    }

    public function getTestUsersData(): array
    {
        return [
            [
                [
                    ['username' => 'test11', 'id' => 'a12s3d', 'delete_at' => 0],
                    ['username' => 'qwe', 'id' => '5g8jf4', 'delete_at' => 100],
                    ['username' => 'fjjt', 'id' => '5gtyy', 'delete_at' => 0],
                ],
                [['username' => 'test11', 'id' => 'a12s3d'], ['username' => 'fjjt', 'id' => '5gtyy']],
            ],
            [
                [
                    ['username' => 'test11', 'id' => 'a12s3d', 'delete_at' => 400],
                    ['username' => 'qwe', 'id' => '5g8jf4', 'delete_at' => 100],
                    ['username' => 'fjjt', 'id' => '5gtyy', 'delete_at' => 1000],
                ],
                [],
            ],
            [
                [],
                [],
            ],
        ];
    }

    public function getTestGroupsData(): array
    {
        return [
            [
                [
                    ['name' => 'test11', 'id' => 'a12s3d', 'delete_at' => 0],
                    ['name' => 'qwe', 'id' => '5g8jf4', 'delete_at' => 100],
                    ['name' => 'fjjt', 'id' => '5gtyy', 'delete_at' => 0],
                ],
                [['name' => 'test11', 'id' => 'a12s3d'], ['name' => 'fjjt', 'id' => '5gtyy']],
            ],
            [
                [
                    ['name' => 'test11', 'id' => 'a12s3d', 'delete_at' => 400],
                    ['name' => 'qwe', 'id' => '5g8jf4', 'delete_at' => 100],
                    ['name' => 'fjjt', 'id' => '5gtyy', 'delete_at' => 1000],
                ],
                [],
            ],
            [
                [],
                [],
            ],
        ];
    }
}
