<?php

namespace LinkORB\OrgSync\Tests\Unit\DTO\Target;

use LinkORB\OrgSync\DTO\Target\Camunda;
use LinkORB\OrgSync\Tests\Helpers\AbstractGettersTestCase;

class CamundaTest extends AbstractGettersTestCase
{
    public function gettersDataProvider(): array
    {
        return [
            ['adminUsername', ''],
            ['adminUsername', null],
            ['adminUsername', 'admin'],
            ['adminPassword', ''],
            ['adminPassword', null],
            ['adminPassword', 'p@ssword'],
        ];
    }

    public function getDefaultArgs(): array
    {
        return [
            'adminPassword' => null,
            'adminUsername' => null,
            'baseUrl' => '',
            'name' => '',
        ];
    }

    public function getDtoClassName(): string
    {
        return Camunda::class;
    }
}
