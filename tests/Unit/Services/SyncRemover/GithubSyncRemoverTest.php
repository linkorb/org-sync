<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Tests\Unit\Services\SyncRemover;

use Github\Api\Organization\Teams;
use Github\Client;
use LinkORB\OrgSync\DTO\Organization;
use LinkORB\OrgSync\DTO\User;
use LinkORB\OrgSync\Services\SyncRemover\GithubSyncRemover;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GithubSyncRemoverTest extends TestCase
{
    /**
     * @var GithubSyncRemover
     */
    private $remover;

    /**
     * @var Client|MockObject
     */
    private $client;

    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);

        $this->remover = new GithubSyncRemover($this->client);

        parent::setUp();
    }

    /**
     * @dataProvider getRemoveData
     */
    public function testRemoveNonExists(array $usersArray, array $teamMembers)
    {
        $users = array_map(
            function (string $username) {
                return new User($username);
            },
            $usersArray
        );

        $expectations = [];
        foreach ($teamMembers as $team => $members) {
            foreach ($members as $member) {
                if (!in_array($member, $usersArray)) {
                    $expectations[] = [$team, $member];
                }
            }
        }

        $team = $this->createMock(Teams::class);

        $this->client->method('__call')
            ->willReturnMap([
                ['teams', [], array_keys($teamMembers)],
                ['team', [], $team]
            ]);

        $team
            ->expects($this->exactly(count($teamMembers)))
            ->method('members')
            ->withConsecutive(...array_map(function (string $teamName) {
                return [$teamName];
            }, array_keys($teamMembers)))
            ->willReturnOnConsecutiveCalls(...array_values($teamMembers));

        $team
            ->expects($this->exactly(count($expectations)))
            ->method('removeMember')
            ->withConsecutive(...$expectations);

        $this->remover->removeNonExists(new Organization('', $users));
    }

    public function getRemoveData(): array
    {
        return [
            [
                ['test1', 'test2', 'test3'],
                [
                    'org1' => [
                        'test1',
                        'test5',
                        'test2',
                    ],
                    'org2' => [
                        'test3',
                    ],
                    'org3' => [
                        'test5',
                        'test6',
                    ]
                ]
            ]
        ];
    }
}
