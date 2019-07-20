<?php

namespace LinkORB\OrgSync\Tests\Unit\SynchronizationAdapter\AdapterFactory;

use InvalidArgumentException;
use LinkORB\OrgSync\SynchronizationAdapter\AdapterFactory\AdapterFactoryInterface;
use LinkORB\OrgSync\SynchronizationAdapter\AdapterFactory\AdapterFactoryPool;
use LinkORB\OrgSync\SynchronizationAdapter\AdapterFactory\CamundaAdapterFactory;
use LinkORB\OrgSync\SynchronizationAdapter\AdapterFactory\GithubAdapterFactory;
use LinkORB\OrgSync\SynchronizationAdapter\AdapterFactory\LdapAdapterFactory;
use LinkORB\OrgSync\SynchronizationAdapter\AdapterFactory\MatterMostAdapterFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AdapterFactoryPoolTest extends TestCase
{
    /** @var AdapterFactoryPool */
    private $pool;

    /** @var MockObject[]|AdapterFactoryInterface[] */
    private $map = [];

    protected function setUp(): void
    {
        $this->map = [
            GithubAdapterFactory::ADAPTER_KEY => $this->createMock(GithubAdapterFactory::class),
            CamundaAdapterFactory::ADAPTER_KEY => $this->createMock(CamundaAdapterFactory::class),
            LdapAdapterFactory::ADAPTER_KEY => $this->createMock(LdapAdapterFactory::class),
            MatterMostAdapterFactory::ADAPTER_KEY => $this->createMock(MatterMostAdapterFactory::class),
        ];

        $this->pool = new AdapterFactoryPool($this->map);

        parent::setUp();
    }

    /**
     * @dataProvider getDataProvider
     */
    public function testGet(string $key)
    {
        $expectedInstance = $this->map[$key];

        $this->assertSame($expectedInstance, $this->pool->get($key));
    }

    public function testGetNonexistingKey()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->pool->get('testKey');
    }

    public function getDataProvider(): array
    {
        return [
            [GithubAdapterFactory::ADAPTER_KEY],
            [CamundaAdapterFactory::ADAPTER_KEY],
            [LdapAdapterFactory::ADAPTER_KEY],
            [MatterMostAdapterFactory::ADAPTER_KEY],
        ];
    }
}
