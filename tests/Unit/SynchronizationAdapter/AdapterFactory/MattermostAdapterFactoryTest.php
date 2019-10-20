<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Tests\Unit\SynchronizationAdapter\AdapterFactory;

use BadMethodCallException;
use Gnello\Mattermost\Driver;
use LinkORB\OrgSync\DTO\Target;
use LinkORB\OrgSync\DTO\Target\Mattermost;
use LinkORB\OrgSync\Services\PasswordHelper;
use LinkORB\OrgSync\Services\SyncRemover\MattermostSyncRemover;
use LinkORB\OrgSync\SynchronizationAdapter\AdapterFactory\MattermostAdapterFactory;
use LinkORB\OrgSync\SynchronizationAdapter\GroupPush\MattermostGroupPushAdapter;
use LinkORB\OrgSync\SynchronizationAdapter\SetPassword\MattermostSetPasswordAdapter;
use LinkORB\OrgSync\SynchronizationAdapter\UserPush\MattermostUserPushAdapter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Pimple\Container;

class MattermostAdapterFactoryTest extends TestCase
{
    /** @var MattermostAdapterFactory|MockObject */
    private $factory;

    /** @var Driver|MockObject */
    private $driver;

    /** @var PasswordHelper|MockObject */
    private $passwordHelper;

    protected function setUp(): void
    {
        $this->driver = $this->createMock(Driver::class);
        $this->passwordHelper = $this->createMock(PasswordHelper::class);

        $this->factory = $this->createPartialMock(MattermostAdapterFactory::class, ['getDriver']);
        $this->factory->method('getDriver')->willReturn($this->driver);
        $this->factory->__construct(null);
        $this->factory->setTarget(new Mattermost('http://test', ''));
    }

    public function testCreateSetPasswordAdapter()
    {
        $this->assertInstanceOf(MattermostSetPasswordAdapter::class, $this->factory->createSetPasswordAdapter());
    }

    public function testCreateSyncRemover()
    {
        $this->assertInstanceOf(MattermostSyncRemover::class, $this->factory->createSyncRemover());
    }

    public function testCreateGroupPushAdapter()
    {
        $this->assertInstanceOf(MattermostGroupPushAdapter::class, $this->factory->createGroupPushAdapter());
    }

    public function testCreateUserPushAdapter()
    {
        $this->assertInstanceOf(MattermostUserPushAdapter::class, $this->factory->createUserPushAdapter());
    }

    /**
     * @dataProvider getAdapterFactoryData
     */
    public function testSetTarget(
        string $scheme,
        string $baseUri,
        ?string $authUsername,
        ?string $authPassword,
        ?string $authToken
    ) {
        $salt = 'some test salt';

        $this->factory = $this->createPartialMock(
            MattermostAdapterFactory::class,
            ['getDriver', 'getPasswordHelper']
        );

        $this->factory
            ->expects($this->once())
            ->method('getPasswordHelper')
            ->with($salt)
            ->willReturn($this->passwordHelper);

        $options = ['url' => $baseUri, 'scheme' => $scheme];

        if ($authToken) {
            $options['token'] = $authToken;
        } else {
            $options['login_id'] = $authUsername;
            $options['password'] = $authPassword;
        }

        $container = new Container(['driver' => $options]);

        $this->factory
            ->expects($this->once())
            ->method('getDriver')
            ->with($container)
            ->willReturn($this->driver);

        $this->factory->__construct($salt);
        $this->factory->setTarget(
            new Mattermost($scheme . '://' . $baseUri, '', $authToken, $authUsername, $authPassword)
        );
    }

    public function testCreateOrganizationPullAdapter()
    {
        $this->expectException(BadMethodCallException::class);

        $this->factory->createOrganizationPullAdapter();
    }

    /**
     * @dataProvider getSupportsData
     */
    public function testSupports(string $action, bool $expected)
    {
        $this->assertEquals($expected, $this->factory->supports($action));
    }

    public function getAdapterFactoryData(): array
    {
        return [
            ['http', 'test.com', 'tt11', null, null],
            ['http', 'test.com', '4t57u', 'name123', null],
            ['http', 'test.com', null, null, '123qwe'],
            ['https', 'temp.nl', null, 'user', 'p@ssword'],
        ];
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
