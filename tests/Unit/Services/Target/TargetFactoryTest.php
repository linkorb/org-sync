<?php

namespace LinkORB\OrgSync\Tests\Unit\Services\Target;

use LinkORB\OrgSync\DTO\Target\Camunda;
use LinkORB\OrgSync\Exception\SyncTargetException;
use LinkORB\OrgSync\Services\Target\TargetFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class TargetFactoryTest extends TestCase
{
    /** @var TargetFactory */
    private $factory;

    /** @var DenormalizerInterface|MockObject */
    private $denormalizer;

    protected function setUp(): void
    {
        $this->denormalizer = $this->createMock(DenormalizerInterface::class);
        $this->factory = new TargetFactory($this->denormalizer);

        parent::setUp();
    }

    public function testCreate()
    {
        $data = [
            'a' => 1,
            'b' => 11,
            'c' => null,
            'type' => 'camunda',
        ];

        $this->denormalizer
            ->expects($this->once())
            ->method('denormalize')
            ->with($data)
            ->willReturn($this->createMock(Camunda::class));

        $this->assertInstanceOf(Camunda::class, $this->factory->create($data));
    }

    public function testCreateException()
    {
        $this->expectException(SyncTargetException::class);

        $this->factory->create([]);
    }
}
