<?php

namespace LinkORB\OrgSync\Tests\Unit\DTO;

use LinkORB\OrgSync\DTO\GroupSegment;
use LinkORB\OrgSync\DTO\Target;
use LinkORB\OrgSync\DTO\User;
use LinkORB\OrgSync\Tests\Helpers\AbstractGettersTestCase;

class GroupSegmentTest extends AbstractGettersTestCase
{
    public function gettersDataProvider(): array
    {
        return [
            ['parent', $this->createMock(GroupSegment::class)],
            ['parent', null],
            ['properties', []],
            ['properties', ['a' => 1, 'b' => 45]],
            ['properties', range(1,20)],
            ['avatar', null],
            ['avatar', ''],
            ['avatar', 'tempName.png'],
            ['members', []],
            ['members', [$this->createMock(User::class)]],
            ['members', [$this->createMock(User::class), $this->createMock(User::class)]],
            ['displayName', ''],
            ['displayName', 'test111'],
            ['targets', []],
            ['targets', [$this->createMock(Target::class)]],
            ['targets', [$this->createMock(Target::class), $this->createMock(Target::class)]],
        ];
    }

    public function getDefaultArgs(): array
    {
        return [
            'displayName' => 'testName',
            'avatar' => null,
            'parent' => null,
            'members' => [],
            'properties' => [],
            'targets' => [],
        ];
    }

    public function getDtoClassName(): string
    {
        return GroupSegment::class;
    }
}
