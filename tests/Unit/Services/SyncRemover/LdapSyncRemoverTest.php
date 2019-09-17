<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Tests\Unit\Services\SyncRemover;

use LinkORB\OrgSync\DTO\Organization;
use LinkORB\OrgSync\DTO\User;
use LinkORB\OrgSync\Services\Ldap\Client;
use LinkORB\OrgSync\Services\SyncRemover\LdapSyncRemover;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LdapSyncRemoverTest extends TestCase
{
    /** @var LdapSyncRemover */
    private $syncRemover;

    /** @var Client|MockObject */
    private $client;

    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
        $this->syncRemover = new LdapSyncRemover($this->client);
    }

    /**
     * @dataProvider getRemoveData
     */
    public function testRemoveNonExists(array $orgUsers, array $existingUsers,array $expectedToRemoveUsers)
    {
        $organization = new Organization('', $orgUsers);

        $searchResult = new \stdClass();
        $this->client
            ->expects($this->once())
            ->method('search')
            ->with('(objectClass=inetOrgPerson)')
            ->willReturn($searchResult);

        $this->client
            ->expects($this->once())
            ->method('all')
            ->with($searchResult)
            ->willReturn($existingUsers);

        $this->client
            ->expects($this->exactly(count($expectedToRemoveUsers)))
            ->method('remove')
            ->withConsecutive(...$expectedToRemoveUsers);

        $this->assertNull($this->syncRemover->removeNonExists($organization));
    }

    public function getRemoveData(): array
    {
        return [
            [
                [
                    new User('test11'),
                    new User('test33'),
                ],
                [
                    'count' => 3,
                    ['cn' => ['test011', 'test11'], 'dn' => 'test11del422'],
                    ['cn' => ['test11'], 'dn' => 'test11del22'],
                    ['cn' => ['test123'], 'dn' => 'test11del99'],
                ],
                [['test11del422'], ['test11del99']],
            ],
            [
                [],
                [
                    'count' => 2,
                    ['cn' => ['test11'], 'dn' => 'test11del22'],
                    ['cn' => ['test123'], 'dn' => 'test11del99'],
                ],
                [['test11del22'], ['test11del99']],
            ],
            [
                [
                    new User('test11'),
                    new User('test33'),
                ],
                [
                    'count' => 0,
                ],
                [],
            ],
            [
                [
                    new User('test11'),
                    new User('test33'),
                ],
                [
                    'count' => 2,
                    ['cn' => ['test11'], 'dn' => 'test11del22'],
                    ['cn' => ['test33'], 'dn' => 'test11del99'],
                ],
                [],
            ],
        ];
    }
}
