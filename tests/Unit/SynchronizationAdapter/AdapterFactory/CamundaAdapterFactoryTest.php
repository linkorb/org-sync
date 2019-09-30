<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Tests\Unit\SynchronizationAdapter\AdapterFactory;

use GuzzleHttp\Client;
use LinkORB\OrgSync\DTO\Target;
use LinkORB\OrgSync\DTO\Target\Camunda;
use LinkORB\OrgSync\SynchronizationAdapter\AdapterFactory\CamundaAdapterFactory;
use LinkORB\OrgSync\Services\PasswordHelper;
use LinkORB\OrgSync\SynchronizationAdapter\GroupPush\CamundaGroupPushAdapter;
use LinkORB\OrgSync\SynchronizationAdapter\SetPassword\CamundaSetPasswordAdapter;
use LinkORB\OrgSync\SynchronizationAdapter\UserPush\CamundaUserPushAdapter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CamundaAdapterFactoryTest extends TestCase
{
    /** @var CamundaAdapterFactory|MockObject */
    private $factory;

    /** @var Client|MockObject */
    private $httpClient;

    /** @var PasswordHelper|MockObject */
    private $passwordHelper;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(Client::class);
        $this->passwordHelper = $this->createMock(PasswordHelper::class);

        $this->factory = $this->createPartialMock(CamundaAdapterFactory::class, ['getClient']);
        $this->factory->method('getClient')->willReturn($this->httpClient);
        $this->factory->__construct(null);
        $this->factory->setTarget(new Camunda('test', ''));

        parent::setUp();
    }

    /**
     * @dataProvider getAdapterFactoryData
     */
    public function testSetTarget(string $baseUri, ?string $authUsername, ?string $authPassword)
    {
        $salt = 'some test salt';

        $this->factory = $this->createPartialMock(
            CamundaAdapterFactory::class,
            ['getClient', 'getPasswordHelper']
        );

        $this->factory
            ->expects($this->once())
            ->method('getPasswordHelper')
            ->with($salt)
            ->willReturn($this->passwordHelper);

        $options = ['base_uri' => $baseUri, 'exceptions' => false];

        if ($authPassword && $authUsername) {
            $options['auth'] = [$authUsername, $authPassword];
        }

        $this->factory
            ->expects($this->once())
            ->method('getClient')
            ->with($options)
            ->willReturn($this->httpClient);

        $this->factory->__construct($salt);
        $this->factory->setTarget(new Camunda($baseUri, '', $authPassword, $authUsername));
    }

    public function testCreateUserPushAdapter()
    {
        $this->assertInstanceOf(CamundaUserPushAdapter::class, $this->factory->createUserPushAdapter());
    }

    public function testCreateOrganizationPullAdapter()
    {
        $this->expectException(\BadMethodCallException::class);

        $this->factory->createOrganizationPullAdapter();
    }

    public function testCreateSetPasswordAdapter()
    {
        $this->assertInstanceOf(CamundaSetPasswordAdapter::class, $this->factory->createSetPasswordAdapter());
    }

    public function testCreateGroupPushAdapter()
    {
       $this->assertInstanceOf(CamundaGroupPushAdapter::class, $this->factory->createGroupPushAdapter());
    }

    public function getAdapterFactoryData(): array
    {
        return [
            ['http://test.com', null, null],
            ['http://test.com', 'name123', null],
            ['http://test.com', null, '123qwe'],
            ['https://temp.nl', 'user', 'p@ssword'],
        ];
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
            [Target::USER_PUSH, true],
            [Target::SET_PASSWORD, true],
            [Target::class, false],
        ];
    }
}
