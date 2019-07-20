<?php

namespace LinkORB\OrgSync\Tests\Unit\DTO;

use LinkORB\OrgSync\DTO\Target;
use LinkORB\OrgSync\Tests\Helpers\AbstractGettersTestCase;

class TargetTest extends AbstractGettersTestCase
{
    public function gettersDataProvider(): array
    {
        return [
            ['name', 'camunda'],
            ['name', ''],
            ['baseUrl', 'http://localhost:8080/camunda'],
            ['baseUrl', ''],
        ];
    }

    public function getDefaultArgs(): array
    {
        return [
            'baseUrl' => 'temp',
            'name' => 'test',
        ];
    }

    public function getDtoClassName(): string
    {
        return Target::class;
    }
}
