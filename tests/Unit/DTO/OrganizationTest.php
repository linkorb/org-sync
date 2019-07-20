<?php

namespace LinkORB\OrgSync\Tests\Unit\DTO;

use LinkORB\OrgSync\DTO\Group;
use LinkORB\OrgSync\DTO\Organization;
use LinkORB\OrgSync\DTO\Target;
use LinkORB\OrgSync\DTO\User;
use LinkORB\OrgSync\Tests\Helpers\AbstractGettersTestCase;

class OrganizationTest extends AbstractGettersTestCase
{
    public function gettersDataProvider(): array
    {
        return [
            ['name', ''],
            ['name', 'temp999'],
            ['users', []],
            ['users', [$this->createMock(User::class)]],
            ['users', [$this->createMock(User::class), $this->createMock(User::class), $this->createMock(User::class)]],
            ['groups', []],
            ['groups', [$this->createMock(Group::class)]],
            ['targets', [$this->createMock(Target::class), $this->createMock(Target::class)]],
            ['targets', []],
        ];
    }

    public function getDefaultArgs(): array
    {
        return [
            'name' => 'test',
            'users' => [],
            'groups' => [],
            'targets' => [],
        ];
    }

    public function getDtoClassName(): string
    {
        return Organization::class;
    }
}
