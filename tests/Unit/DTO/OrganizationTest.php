<?php

namespace LinkORB\OrgSync\Tests\Unit\DTO;

use LinkORB\OrgSync\DTO\Group;
use LinkORB\OrgSync\DTO\Organization;
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
        ];
    }

    public function getDefaultArgs(): array
    {
        return [
            'name' => 'test',
            'users' => [],
            'groups' => [],
        ];
    }

    public function getDtoClassName(): string
    {
        return Organization::class;
    }
}
