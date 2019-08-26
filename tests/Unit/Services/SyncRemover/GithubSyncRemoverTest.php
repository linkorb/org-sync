<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Tests\Unit\Services\SyncRemover;

use Github\Api\Organization\Teams;
use Github\Client;
use LinkORB\OrgSync\DTO\Group;
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
    public function testRemoveNonExists(array $usersArray, array $teamMembers, array $orgGroups)
    {
        $orgName = 'TempOrg';

        $orgGroupDtos = array_map(function (string $name) {
            return new Group($name, '');
        }, $orgGroups);
        $users = array_map(
            function (string $username) {
                return new User($username);
            },
            $usersArray
        );

        $expectations = [];
        foreach ($teamMembers as $team => $members) {
            if (!in_array($team, $orgGroups)) {
                continue;
            }

            foreach ($members as $member) {
                if (!in_array($member, $usersArray)) {
                    $expectations[] = [$team, $member];
                }
            }
        }

        $team = $this->createMock(Teams::class);
        $teams = $this->createConfiguredMock(
            Teams::class,
            [
                'all' => array_map(function (string $name) {
                    return ['name' => $name];
                }, array_keys($teamMembers))
            ]
        );

        $this->client->method('__call')
            ->willReturnMap([
                ['teams', [], $teams],
                ['team', [], $team]
            ]);

        $membersExpectations = array_intersect_key($teamMembers, array_flip($orgGroups));
        $team
            ->expects($this->exactly(count($membersExpectations)))
            ->method('members')
            ->withConsecutive(...array_map(function (string $teamName) {
                return [$teamName];
            }, array_keys($membersExpectations)))
            ->willReturnOnConsecutiveCalls(...array_values($membersExpectations));

        $team
            ->expects($this->exactly(count($expectations)))
            ->method('removeMember')
            ->withConsecutive(...$expectations);

        $groupsToDelete = array_map(function (string $name) {
            return [$name];
        }, array_diff(array_keys($teamMembers), $orgGroups));

        $team
            ->expects($this->exactly(count($groupsToDelete)))
            ->method('remove')
            ->withConsecutive(...array_values($groupsToDelete));

        $this->remover->removeNonExists(new Organization($orgName, $users, $orgGroupDtos));
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
                    ],
                    'org4' => [
                        'test7',
                    ]
                ],
                ['org1', 'org2', 'org3']
            ]
        ];
    }
}
