<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Tests\Unit\AdapterFactory;

use GuzzleHttp\Client;
use LinkORB\OrgSync\AdapterFactory\CamundaAdapterFactory;
use LinkORB\OrgSync\Services\PasswordHelper;
use LinkORB\OrgSync\SynchronizationAdapter\GroupPush\CamundaGroupPushAdapter;
use LinkORB\OrgSync\SynchronizationAdapter\OrganizationPush\CamundaOrganizationPushAdapter;
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
        $this->factory->__construct('test', null, null, null);

        parent::setUp();
    }

    /**
     * @dataProvider getAdapterFactoryData
     */
    public function testConstruct(string $baseUri, ?string $authUsername, ?string $authPassword)
    {
        $salt = 'some test salt';
        $options = ['base_uri' => $baseUri];

        if ($authPassword && $authUsername) {
            $options['auth'] = [$authUsername, $authPassword];
        }

        $this->factory = $this->createPartialMock(CamundaAdapterFactory::class, ['getClient', 'getPasswordHelper']);
        $this->factory
            ->expects($this->once())
            ->method('getClient')
            ->with($options)
            ->willReturn($this->httpClient);
        $this->factory
            ->expects($this->once())
            ->method('getPasswordHelper')
            ->with($salt)
            ->willReturn($this->passwordHelper);

        $this->factory->__construct($baseUri, $authUsername, $authPassword, $salt);
    }

    public function testCreateUserPushAdapter()
    {
        $this->assertInstanceOf(CamundaUserPushAdapter::class, $this->factory->createUserPushAdapter());
    }

    public function testCreateOrganizationPullAdapter()
    {
        $this->markTestSkipped('Need to implement');
    }

    public function testCreateSetPasswordAdapter()
    {
        $this->assertInstanceOf(CamundaSetPasswordAdapter::class, $this->factory->createSetPasswordAdapter());
    }

    public function testCreateOrganizationPushAdapter()
    {
        $this->assertInstanceOf(CamundaOrganizationPushAdapter::class, $this->factory->createOrganizationPushAdapter());
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
}
