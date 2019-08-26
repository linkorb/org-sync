<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Tests\Unit\SynchronizationAdapter\AdapterFactory;

use Github\Client;
use LinkORB\OrgSync\DTO\Target;
use LinkORB\OrgSync\Services\SyncRemover\GithubSyncRemover;
use LinkORB\OrgSync\SynchronizationAdapter\AdapterFactory\GithubAdapterFactory;
use LinkORB\OrgSync\SynchronizationAdapter\GroupPush\GithubGroupPushAdapter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GithubAdapterFactoryTest extends TestCase
{
    /**
     * @var GithubAdapterFactory
     */
    private $factory;

    /**
     * @var Client|MockObject
     */
    private $client;

    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);

        $this->factory = new GithubAdapterFactory($this->client);

        parent::setUp();
    }

    public function testCreateUserPushAdapter()
    {
        $this->expectException(\BadMethodCallException::class);

        $this->factory->createUserPushAdapter();
    }

    public function testCreateOrganizationPullAdapter()
    {
        $this->expectException(\BadMethodCallException::class);

        $this->factory->createOrganizationPullAdapter();
    }

    public function testCreateSetPasswordAdapter()
    {
        $this->expectException(\BadMethodCallException::class);

        $this->factory->createSetPasswordAdapter();
    }

    public function testCreateGroupPushAdapter()
    {
        $this->assertInstanceOf(GithubGroupPushAdapter::class, $this->factory->createGroupPushAdapter());
    }

    public function testSetTarget()
    {
        $token = '123qwetest';

        $this->client->expects($this->once())->method('authenticate')->with($token, Client::AUTH_HTTP_TOKEN);

        $this->factory->setTarget(new Target\Github('', '', $token));
    }

    public function testCreateSyncRemover()
    {
        $this->assertInstanceOf(GithubSyncRemover::class, $this->factory->createSyncRemover());
    }

    /**
     * @dataProvider getSupportsData
     */
    public function testSupports(string $action, bool $expected)
    {
        $this->assertEquals($expected, $this->factory->supports($action));
    }

    public function getSupportsData(): array
    {
        return [
            [Target::GROUP_PUSH, true],
            [Target::PULL_ORGANIZATION, false],
            [Target::USER_PUSH, false],
        ];
    }
}
