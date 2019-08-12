<?php

namespace LinkORB\OrgSync\Tests\Unit\Services\SyncRemover;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use LinkORB\OrgSync\DTO\User;
use LinkORB\OrgSync\Services\Camunda\CamundaGroupMemberProvider;
use LinkORB\OrgSync\Services\Camunda\CamundaGroupProvider;
use LinkORB\OrgSync\Services\Camunda\CamundaUserProvider;
use LinkORB\OrgSync\Services\SyncRemover\CamundaSyncRemover;
use LinkORB\OrgSync\Tests\Helpers\OrganizationDataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class CamundaSyncRemoverTest extends TestCase
{
    /**
     * @var CamundaSyncRemover
     */
    private $syncRemover;

    /**
     * @var CamundaUserProvider|MockObject
     */
    private $userProvider;

    /**
     * @var CamundaGroupProvider|MockObject
     */
    private $groupProvider;

    /**
     * @var CamundaGroupMemberProvider|MockObject
     */
    private $userGroupsProvider;

    /**
     * @var Client|MockObject
     */
    private $client;

    protected function setUp(): void
    {
        $this->userProvider = $this->createMock(CamundaUserProvider::class);
        $this->groupProvider = $this->createMock(CamundaGroupProvider::class);
        $this->userGroupsProvider = $this->createMock(CamundaGroupMemberProvider::class);
        $this->client = $this->createMock(Client::class);

        $this->syncRemover = new CamundaSyncRemover(
            $this->userProvider,
            $this->groupProvider,
            $this->userGroupsProvider,
            $this->client
        );

        parent::setUp();
    }

    public function testRemoveNonExists()
    {
        $organization = OrganizationDataProvider::provideDto();

        $this->userProvider->expects($this->once())->method('getUsers')->willReturn(array_merge(
            $organization->getUsers(),
            OrganizationDataProvider::transformToUsers([
                'user_old' => [
                    'email' => 'user_old@linkorb.com',
                    'displayName' => 'Old account',
                ],
                'joe33' => [
                    'email' => 'joe+33@example.com',
                ]
            ])
        ));

        $orgUserIndex = 2;
        $orgUsername = $organization->getUsers()[$orgUserIndex]->getUsername();
        $orgGroup = clone $organization->getGroups()[1];
        $orgGroup->addMember(new User($orgUsername));

        $existingGroups = array_merge(
            $organization->getGroups(),
            OrganizationDataProvider::transformToGroups([
                'team_old' => [
                    'displayName' => 'the whole old team',
                ],
                'developers_old' => [
                    'parent' => 'team_old',
                    'displayName' => 'Developers',
                    'members' => [
                    ],
                    'properties' => [
                    ],
                    'targets' => [
                    ]
                ]
            ], [], [])
        );
        $existingGroups[1] = $orgGroup;

        $this->groupProvider->expects($this->once())->method('getGroups')->willReturn($existingGroups);

        $returnMap = array_fill(0, count($organization->getUsers()), []);
        $returnMap[$orgUserIndex] = [$orgGroup];

        $this->userGroupsProvider
            ->expects($this->exactly(count($organization->getUsers())))
            ->method('getGroupsForUser')
            ->willReturnOnConsecutiveCalls(...$returnMap);

        $this->client
            ->expects($this->exactly(5))
            ->method('__call')
            ->withConsecutive(
                ['delete', ['user/user_old']],
                ['delete', ['user/joe33']],
                ['delete', ['group/team_old']],
                ['delete', ['group/developers_old']],
                ['delete', ['group/' . $orgGroup->getName() . '/members/' . $orgUsername]],
            );

        $this->assertNull($this->syncRemover->removeNonExists($organization));
    }
}
