<?php

namespace LinkORB\OrgSync\Tests\Unit\SynchronizationAdapter\SetPassword;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\RequestOptions;
use LinkORB\OrgSync\DTO\User;
use LinkORB\OrgSync\Exception\SyncHttpException;
use LinkORB\OrgSync\Services\Camunda\ResponseChecker;
use LinkORB\OrgSync\Services\PasswordHelper;
use LinkORB\OrgSync\SynchronizationAdapter\SetPassword\CamundaSetPasswordAdapter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class CamundaSetPasswordAdapterTest extends TestCase
{
    /** @var CamundaSetPasswordAdapter */
    private $adapter;

    /** @var Client|MockObject */
    private $httpClient;

    /** @var PasswordHelper */
    private $passwordHelper;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(Client::class);
        $this->passwordHelper = new PasswordHelper(null);

        $this->adapter = new CamundaSetPasswordAdapter($this->httpClient, $this->passwordHelper, new ResponseChecker());

        parent::setUp();
    }

    public function testSetPassword()
    {
        $id = 'testId';

        $user = $this->createConfiguredMock(User::class, ['getUsername' => $id]);
        $password = '0123456789';

        $this->httpClient
            ->expects($this->once())
            ->method('__call')
            ->with(
                'put',
                [
                    sprintf('/user/%s/credentials', $id),
                    [
                        RequestOptions::JSON => [
                            'password' => $password,
                            'authenticatedUserPassword' => $this->passwordHelper->getDefaultPassword($id),
                        ],
                    ]
                ]
            )
            ->willReturn($this->createConfiguredMock(ResponseInterface::class, ['getStatusCode' => 200]));

        $this->assertSame($this->adapter, $this->adapter->setPassword($user, $password));
    }

    public function testSetPasswordHttpException()
    {
        $user = $this->createMock(User::class);
        $guzzleException = new ConnectException('', $this->createMock(RequestInterface::class));

        $this->httpClient->method('__call')->willThrowException($guzzleException);

        $this->expectExceptionObject(new SyncHttpException($guzzleException));

        $this->adapter->setPassword($user, '');
    }
}
