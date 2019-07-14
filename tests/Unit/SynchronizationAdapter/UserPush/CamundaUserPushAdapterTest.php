<?php

namespace LinkORB\OrgSync\Tests\Unit\SynchronizationAdapter\UserPush;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use LinkORB\OrgSync\DTO\User;
use LinkORB\OrgSync\Exception\SyncHttpException;
use LinkORB\OrgSync\Exception\UserSyncException;
use LinkORB\OrgSync\Services\Camunda\ResponseChecker;
use LinkORB\OrgSync\Services\PasswordHelper;
use LinkORB\OrgSync\SynchronizationAdapter\UserPush\CamundaUserPushAdapter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class CamundaUserPushAdapterTest extends TestCase
{
    /** @var MockObject|CamundaUserPushAdapter */
    private $adapter;

    /** @var MockObject|Client */
    private $httpClient;

    /** @var PasswordHelper */
    private $passwordHelper;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(Client::class);
        $this->passwordHelper = new PasswordHelper(null);

        $this->adapter = new CamundaUserPushAdapter(
            $this->httpClient,
            $this->passwordHelper,
            new ResponseChecker()
        );

        parent::setUp();
    }

    public function testCreate()
    {
        list($id, $firstName, $lastName, $email, $user) = $this->getUserData();
        $password = $this->passwordHelper->getDefaultPassword($id);

        $this->httpClient
            ->expects($this->exactly(2))
            ->method('__call')
            ->withConsecutive(
                ['get', [sprintf('/user/%s/profile', $id)]],
                [
                    'post',
                    ['/user/create',
                        [
                            RequestOptions::JSON => [
                                'profile' => [
                                    'id' => $id,
                                    'firstName' => $firstName,
                                    'lastName' => $lastName,
                                    'email' => $email,
                                ],
                                'credentials' => [
                                    'password' => $password,
                                ],
                            ],
                        ]
                    ]
                ]
            )
            ->willReturnOnConsecutiveCalls(
                $this->createConfiguredMock(ResponseInterface::class, ['getStatusCode' => 404]),
                $this->createConfiguredMock(ResponseInterface::class, ['getStatusCode' => 200])
            );

        $this->assertSame($this->adapter, $this->adapter->pushUser($user));
    }

    /**
     * @dataProvider getExceptionData
     */
    public function testCreateHttpException(bool $exists)
    {
        $statusCode = 400;
        $body = '{"error":"fatal"}';

        $this->httpClient
            ->method('__call')
            ->willReturnOnConsecutiveCalls(
                $this->createConfiguredMock(Response::class, ['getStatusCode' => $exists ? 200 : 404]),
                $this->createConfiguredMock(Response::class, ['getStatusCode' => $statusCode, 'getBody' => $body])
            );

        $this->expectException(UserSyncException::class);
        $this->expectExceptionMessage($body);
        $this->expectExceptionCode($statusCode);

        $this->adapter->pushUser($this->createConfiguredMock(User::class, ['getProperties' => []]));
    }

    /**
     * @dataProvider getExceptionData
     */
    public function testCreateUserSyncException(bool $exists)
    {
        $guzzleException = new ConnectException('no connection', $this->createMock(Request::class));

        $this->httpClient->method('__call')->willReturnOnConsecutiveCalls(
            $this->createConfiguredMock(Response::class, ['getStatusCode' => $exists ? 200 : 404]),
            $this->throwException($guzzleException)
        );

        $this->expectExceptionObject(new SyncHttpException($guzzleException));

        $this->adapter->pushUser($this->createConfiguredMock(User::class, ['getProperties' => []]));
    }

    public function testUpdate()
    {
        list($id, $firstName, $lastName, $email, $user) = $this->getUserData();

        $this->httpClient
            ->expects($this->exactly(2))
            ->method('__call')
            ->withConsecutive(
                ['get', [sprintf('/user/%s/profile', $id)]],
                [
                    'put',
                    [
                        sprintf('/user/%s/profile', $id),
                        [
                            RequestOptions::JSON => [
                                'id' => $id,
                                'firstName' => $firstName,
                                'lastName' => $lastName,
                                'email' => $email,
                            ],
                        ]
                    ]
                ]
            )
            ->willReturn($this->createConfiguredMock(ResponseInterface::class, ['getStatusCode' => 200]));

        $this->assertSame($this->adapter, $this->adapter->pushUser($user));
    }

    public function getExceptionData(): array
    {
        return [
            [true],
            [false],
        ];
    }

    private function getUserData(): array
    {
        $id = 'testId';
        $firstName = 'John';
        $lastName = 'Doe';
        $email = 'john.doe@gmail.com';

        $user = $this->createConfiguredMock(
            User::class,
            [
                'getUsername' => $id,
                'getEmail' => $email,
                'getProperties' => [
                    'firstName' => $firstName,
                    'lastName' => $lastName,
                ],
            ]
        );

        return [$id, $firstName, $lastName, $email, $user];
    }
}
