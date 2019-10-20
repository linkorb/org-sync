<?php

namespace LinkORB\OrgSync\Tests\Unit\DTO;

use LinkORB\OrgSync\DTO\Group;
use LinkORB\OrgSync\DTO\Target;
use LinkORB\OrgSync\DTO\User;
use LinkORB\OrgSync\Tests\Helpers\AbstractGettersTestCase;

class GroupTest extends AbstractGettersTestCase
{
    public function gettersDataProvider(): array
    {
        return [
            ['name', ''],
            ['name', 'test name'],
            ['parent', $this->createMock(Group::class)],
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
            'name' => 'test',
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
        return Group::class;
    }

    /**
     * @dataProvider getAddPropertyData
     */
    public function testAddProperty(string $key, $value, $expected, bool $override, array $defaultProps)
    {
        $group = new Group('', '', null, null, [], $defaultProps);

        $group->addProperty($key, $value, $override);

        $this->assertEquals($expected, $group->getProperties()[$key]);
    }

    public function getAddPropertyData(): array
    {
        return [
            ['test', 1, 1, true, [],],
            ['test1', false, false, true, ['test1' => true],],
            ['test2', false, true, false, ['test2' => true, 'test1' => false,]],
            ['qwerty', 'temp', 'temp', false, ['test1' => false,]],
            ['qwerty', '123', 'temp', false, ['qwerty' => 'temp',]],
        ];
    }
}
