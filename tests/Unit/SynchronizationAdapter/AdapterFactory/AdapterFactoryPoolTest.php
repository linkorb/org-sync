<?php

namespace LinkORB\OrgSync\Tests\Unit\SynchronizationAdapter\AdapterFactory;

use InvalidArgumentException;
use LinkORB\OrgSync\DTO\Target;
use LinkORB\OrgSync\DTO\Target\Camunda;
use LinkORB\OrgSync\SynchronizationAdapter\AdapterFactory\AdapterFactoryInterface;
use LinkORB\OrgSync\SynchronizationAdapter\AdapterFactory\AdapterFactoryPool;
use LinkORB\OrgSync\SynchronizationAdapter\AdapterFactory\CamundaAdapterFactory;
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
            Camunda::class => $this->createMock(CamundaAdapterFactory::class),
        ];

        $this->pool = new AdapterFactoryPool($this->map);

        parent::setUp();
    }

    /**
     * @dataProvider getDataProvider
     */
    public function testGet(Target $target)
    {
        $expectedInstance = $this->map[get_class($target)];

        $this->map[get_class($target)]->expects($this->once())->method('setTarget')->with($target)->willReturnSelf();

        $this->assertSame($expectedInstance, $this->pool->get($target));
    }

    public function testGetNonexistingKey()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->pool->get($this->createMock(Target::class));
    }

    public function getDataProvider(): array
    {
        return [
            [new Camunda('http://localhost', 'test')],
        ];
    }
}
