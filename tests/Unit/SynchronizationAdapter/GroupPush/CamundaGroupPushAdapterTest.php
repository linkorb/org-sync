<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Tests\Unit\SynchronizationAdapter\GroupPush;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Exception;
use LinkORB\OrgSync\DTO\Group;
use LinkORB\OrgSync\DTO\User;
use LinkORB\OrgSync\Exception\GroupSyncException;
use LinkORB\OrgSync\Services\ResponseChecker;
use LinkORB\OrgSync\SynchronizationAdapter\GroupPush\CamundaGroupPushAdapter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class CamundaGroupPushAdapterTest extends TestCase
{
    /** @var CamundaGroupPushAdapter */
    private $adapter;

    /** @var Client|MockObject */
    private $httpClient;

    /** @var ResponseChecker */
    private $responseChecker;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(Client::class);
        $this->responseChecker = new ResponseChecker(Group::class);

        $this->adapter = new CamundaGroupPushAdapter($this->httpClient, $this->responseChecker);

        parent::setUp();
    }

    /**
     * @dataProvider getGroupData
     */
    public function testPushGroup(Group $group, bool $create)
    {
        $consecutiveArgs = [
            ['get', [sprintf('group/%s', $group->getName())]],
            [
                $create ? 'post' : 'put',
                [
                    'group/' . ($create ? 'create' : $group->getName()),
                    [
                        RequestOptions::JSON => [
                            'id' => $group->getName(),
                            'name' => $group->getDisplayName(),
                            'type' =>  null,
                        ],
                    ]
                ]
            ]
        ];

        $returnOnConsecutive = [
            $this->createConfiguredMock(ResponseInterface::class, ['getStatusCode' => $create ? 404 : 200]),
            $this->createConfiguredMock(ResponseInterface::class, ['getStatusCode' => 200]),
        ];

        foreach ($group->getMembers() as $key => $member) {
            $consecutiveArgs[] = ['put', [sprintf('group/%s/members/%s', $group->getName(), $member->getUsername())]];
            $returnOnConsecutive[] = $this->createConfiguredMock(
                ResponseInterface::class,
                ['getStatusCode' => ($key % 2 === 0) ? 404 : 201]
            );
        }

        $this->httpClient
            ->expects($this->exactly(2 + count($group->getMembers())))
            ->method('__call')
            ->withConsecutive(...$consecutiveArgs)
            ->willReturnOnConsecutiveCalls(...$returnOnConsecutive);

        $this->assertInstanceOf(CamundaGroupPushAdapter::class, $this->adapter->pushGroup($group));
    }

    public function testPushExistsHttpException()
    {
        $this->expectException(Exception::class);
        $group = $this->getGroup();

        $this->httpClient
            ->expects($this->once())
            ->method('__call')
            ->willThrowException(new Exception());

        $this->adapter->pushGroup($group);
    }

   public function testPushGroupHttpException()
    {
        $this->expectException(Exception::class);
        $group = $this->getGroup();

        $this->httpClient
            ->expects($this->exactly(2))
            ->method('__call')
            ->willReturnOnConsecutiveCalls(
                $this->createMock(ResponseInterface::class),
                $this->throwException(new Exception())
            );

        $this->adapter->pushGroup($group);
    }

    public function testPushInvalidGroupHttpException()
    {
        $this->expectException(GroupSyncException::class);
        $group = $this->getGroup();

        $this->httpClient
            ->expects($this->exactly(2))
            ->method('__call')
            ->willReturnOnConsecutiveCalls(
                $this->createMock(ResponseInterface::class),
                $this->createConfiguredMock(ResponseInterface::class, ['getStatusCode' => 404])
            );

        $this->adapter->pushGroup($group);
    }

    public function getGroupData(): array
    {
        return [
            [$this->getGroup(), true],
            [$this->getGroup(), false],
        ];
    }

    private function getGroup(): Group
    {
        return new Group(
            'test123',
            'Test name!',
            '123.jpeg',
            new Group('parent_test', 'Parent'),
            [
                new User('joe', null),
                new User('phil', null),
            ]
        );
    }
}
