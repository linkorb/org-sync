<?php

namespace LinkORB\OrgSync\Tests\Unit\SynchronizationAdapter\GroupPush;

use Github\Api\Organization\Teams;
use Github\Client;
use Http\Client\Common\HttpMethodsClient;
use Http\Client\Exception\TransferException;
use LinkORB\OrgSync\DTO\Group;
use LinkORB\OrgSync\DTO\User;
use LinkORB\OrgSync\Services\InputHandler;
use LinkORB\OrgSync\SynchronizationAdapter\GroupPush\GithubGroupPushAdapter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

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

    /** @var HttpMethodsClient|MockObject */
    private $httpClient;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpMethodsClient::class);
        $this->client = $this->createConfiguredMock(Client::class, ['getHttpClient' => $this->httpClient]);

        $this->groupPush = new GithubGroupPushAdapter($this->client);

        parent::setUp();
    }

    public function testCreateGroup()
    {
        $orgName = 'test113';
        $name = 'testing';
        $id = 99;
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

        $this->httpClient->expects($this->once())->method('get')->willThrowException(new TransferException());
        $this->client->expects($this->once())->method('getHttpClient');

        $team->expects($this->never())
            ->method('update');
        $team->expects($this->once())->method('create')->with($orgName, $params)->willReturn(['id' => $id]);

        $team->expects($this->exactly(count($membersData)))
            ->method('addMember')
            ->withConsecutive(...array_map(function (string $username) use ($id) {
                return [$id, $username];
            }, $membersData));

        $this->assertSame($this->groupPush, $this->groupPush->pushGroup($group));
    }

    public function testUpdate()
    {
        $orgName = 'test12';
        $id = 12;
        $group = new Group('name', '');
        $group->addProperty(InputHandler::GITHUB_ORGANIZATION, $orgName);

        $team = $this->createMock(Teams::class);

        $this->client->method('__call')
            ->with('team', [])
            ->willReturn($team);

        $responseData = json_encode(['id' => $id]);
        $response = $this->createConfiguredMock(
            ResponseInterface::class,
            [
                'getBody' => $this->createConfiguredMock(
                    StreamInterface::class,
                    ['getContents' => $responseData]
                )
            ]
        );
        $this->httpClient->expects($this->once())->method('get')->willReturn($response);
        $team->expects($this->once())
            ->method('update')
            ->with($id, $this->anything())
            ->willReturn(json_encode($responseData));
        $team->expects($this->never())->method('create');

        $this->assertSame($this->groupPush, $this->groupPush->pushGroup($group));
    }
}
