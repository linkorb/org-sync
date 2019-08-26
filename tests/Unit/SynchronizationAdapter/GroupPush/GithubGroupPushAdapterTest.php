<?php

namespace LinkORB\OrgSync\Tests\Unit\SynchronizationAdapter\GroupPush;

use Github\Api\Organization\Teams;
use Github\Client;
use Http\Client\Exception\TransferException;
use LinkORB\OrgSync\DTO\Group;
use LinkORB\OrgSync\DTO\User;
use LinkORB\OrgSync\Services\InputHandler;
use LinkORB\OrgSync\SynchronizationAdapter\GroupPush\GithubGroupPushAdapter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GithubGroupPushAdapterTest extends TestCase
{
    /**
     * @var GithubGroupPushAdapter
     */
    private $groupPush;

    /**
     * @var Client|MockObject
     */
    private $client;

    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);

        $this->groupPush = new GithubGroupPushAdapter($this->client);

        parent::setUp();
    }

    public function testCreateGroup()
    {
        $orgName = 'test113';
        $name = 'testing';
        $membersData = [
            'Tom',
            'Jerry',
            'Spike',
        ];

        $params = [
            'name' => $name,
        ];

        $group = new Group($name, '', null, null, array_map(function (string $username) {
            return new User($username);
        }, $membersData), [InputHandler::GITHUB_ORGANIZATION => $orgName]);

        $team = $this->createMock(Teams::class);

        $this->client->method('__call')
            ->with('team', [])
            ->willReturn($team);

        $team->expects($this->once())
            ->method('update')
            ->with($orgName, $params)
            ->willThrowException(new TransferException());
        $team->expects($this->once())->method('create')->with($orgName, $params);

        $team->expects($this->exactly(count($membersData)))
            ->method('addMember')
            ->withConsecutive(...array_map(function (string $username) use ($name) {
                return [$name, $username];
            }, $membersData));

        $this->assertSame($this->groupPush, $this->groupPush->pushGroup($group));
    }

    public function testUpdate()
    {
        $orgName = 'test12';
        $group = new Group('name', '');
        $group->addProperty(InputHandler::GITHUB_ORGANIZATION, $orgName);

        $team = $this->createMock(Teams::class);

        $this->client->method('__call')
            ->with('team', [])
            ->willReturn($team);

        $team->expects($this->once())->method('update');
        $team->expects($this->never())->method('create');

        $this->assertSame($this->groupPush, $this->groupPush->pushGroup($group));
    }
}
