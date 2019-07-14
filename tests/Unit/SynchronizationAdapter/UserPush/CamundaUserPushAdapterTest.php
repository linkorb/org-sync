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
use LinkORB\OrgSync\SynchronizationAdapter\UserPush\CamundaUserPushAdapter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class CamundaUserPushAdapterTest extends TestCase
{
    /** @var MockObject|CamundaUserPushAdapter */
    private $camundaAdapter;

    /** @var MockObject|Client */
    private $httpClient;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(Client::class);
        $this->camundaAdapter = $this->createPartialMock(CamundaUserPushAdapter::class, ['exists', 'generatePassword']);
        $this->camundaAdapter->__construct($this->httpClient);

        parent::setUp();
    }

    public function testCreate()
    {
        list($id, $firstName, $lastName, $email, $user) = $this->getUserData();
        $password = '0123456789';

        $this->camundaAdapter->expects($this->once())->method('exists')->with($user)->willReturn(false);
        $this->camundaAdapter->expects($this->once())->method('generatePassword')->willReturn($password);

        $this->httpClient
            ->expects($this->once())
            ->method('__call')
            ->with(
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
            )
            ->willReturn($this->createConfiguredMock(ResponseInterface::class, ['getStatusCode' => 200]));

        $this->assertSame($this->camundaAdapter, $this->camundaAdapter->pushUser($user));
    }

    /**
     * @dataProvider getExceptionData
     */
    public function testCreateHttpException(bool $exists)
    {
        $this->camundaAdapter->method('exists')->willReturn($exists);

        $statusCode = 400;
        $body = '{"error":"fatal"}';

        $this->httpClient
            ->method('__call')
            ->willReturn(
                $this->createConfiguredMock(Response::class, ['getStatusCode' => $statusCode, 'getBody' => $body])
            );

        $this->expectException(UserSyncException::class);
        $this->expectExceptionMessage($body);
        $this->expectExceptionCode($statusCode);

        $this->camundaAdapter->pushUser($this->createConfiguredMock(User::class, ['getProperties' => []]));
    }

    /**
     * @dataProvider getExceptionData
     */
    public function testCreateUserSyncException(bool $exists)
    {
        $this->camundaAdapter->method('exists')->willReturn($exists);

        $guzzleException = new ConnectException('no connection', $this->createMock(Request::class));

        $this->httpClient->method('__call')->willThrowException($guzzleException);

        $this->expectExceptionObject(new SyncHttpException($guzzleException));

        $this->camundaAdapter->pushUser($this->createConfiguredMock(User::class, ['getProperties' => []]));
    }

    public function testUpdate()
    {
        list($id, $firstName, $lastName, $email, $user) = $this->getUserData();

        $this->camundaAdapter->expects($this->once())->method('exists')->with($user)->willReturn(true);

        $this->httpClient
            ->expects($this->once())
            ->method('__call')
            ->with(
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
            )
            ->willReturn($this->createConfiguredMock(ResponseInterface::class, ['getStatusCode' => 200]));

        $this->assertSame($this->camundaAdapter, $this->camundaAdapter->pushUser($user));
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
