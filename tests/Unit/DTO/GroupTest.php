<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Tests\Unit\DTO;

use LinkORB\OrgSync\DTO\Group;
use LinkORB\OrgSync\DTO\GroupSegment;
use LinkORB\OrgSync\Tests\Helpers\AbstractGettersTestCase;

class GroupTest extends AbstractGettersTestCase
{
    public function gettersDataProvider(): array
    {
        return [
            ['segments', [$this->createMock(GroupSegment::class), $this->createMock(GroupSegment::class)]],
            ['segments', []],
            [
                'segments',
                [
                    'a' => $this->createMock(GroupSegment::class),
                    'b' => $this->createMock(GroupSegment::class),
                ]
            ],
        ];
    }

    public function getDtoClassName(): string
    {
        return Group::class;
    }

    public function getDefaultArgs(): array
    {
        return [
            'segments' => [],
        ];
    }
}
