<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Tests\Unit\AdapterFactory;

use GuzzleHttp\Client;
use LinkORB\OrgSync\AdapterFactory\CamundaAdapterFactory;
use LinkORB\OrgSync\SynchronizationAdapter\UserPush\UserPushInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CamundaAdapterFactoryTest extends TestCase
{
    /** @var CamundaAdapterFactory|MockObject */
    private $factory;

    /** @var Client|MockObject */
    private $httpClient;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(Client::class);

        $this->factory = $this->createPartialMock(CamundaAdapterFactory::class, ['getClient']);
        $this->factory->method('getClient')->willReturn($this->httpClient);
        $this->factory->__construct('test', null, null);

        parent::setUp();
    }

    /**
     * @dataProvider getAdapterFactoryData
     */
    public function testConstruct(string $baseUri, ?string $authUsername, ?string $authPassword)
    {
        $options = ['base_uri' => $baseUri];

        if ($authPassword && $authUsername) {
            $options['auth'] = [$authUsername, $authPassword];
        }

        $this->factory = $this->createPartialMock(CamundaAdapterFactory::class, ['getClient']);
        $this->factory->expects($this->once())->method('getClient')->with($options)->willReturn($this->httpClient);

        $this->factory->__construct($baseUri, $authUsername, $authPassword);
    }

    public function testCreateUserPushAdapter()
    {
        $this->assertInstanceOf(UserPushInterface::class, $this->factory->createUserPushAdapter());
    }

    public function testCreateOrganizationPullAdapter()
    {
        $this->markTestSkipped('TODO: add');
    }

    public function testCreateSetPasswordAdapter()
    {
        $this->markTestSkipped('TODO: add');
    }

    public function testCreateOrganizationPushAdapter()
    {
        $this->markTestSkipped('TODO: add');
    }

    public function testCreateGroupPushAdapter()
    {
        $this->markTestSkipped('TODO: add');
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
