<?php

namespace LinkORB\OrgSync\Tests\Unit\SynchronizationAdapter\UserPush;

use Github\Client;
use LinkORB\OrgSync\DTO\User;
use LinkORB\OrgSync\SynchronizationAdapter\UserPush\GithubUserPushAdapter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GithubUserPushAdapterTest extends TestCase
{
    /** @var GithubUserPushAdapter */
    private $githubPushAdapter;

    /** @var Client|MockObject */
    private $client;

    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
        $this->githubPushAdapter = new GithubUserPushAdapter($this->client);

        parent::setUp();
    }

    public function testPush()
    {
        $this->markTestSkipped();
        $user = $this->createMock(User::class);

//        $this->client->user()->

        $this->assertSame($this->githubPushAdapter, $this->githubPushAdapter->pushUser($user));
    }
}
