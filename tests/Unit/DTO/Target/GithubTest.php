<?php

namespace LinkORB\OrgSync\Tests\Unit\DTO\Target;

use LinkORB\OrgSync\DTO\Target\Github;
use LinkORB\OrgSync\Tests\Helpers\AbstractGettersTestCase;

class GithubTest extends AbstractGettersTestCase
{
    public function gettersDataProvider(): array
    {
        return [
            ['token', ''],
            ['token', '1234qwerty'],
            ['name', 'camunda'],
            ['name', ''],
            ['baseUrl', 'http://localhost:8080/camunda'],
            ['baseUrl', ''],
        ];
    }

    public function getDefaultArgs(): array
    {
        return [
            'baseUrl' => '',
            'name' => '',
            'token' => '',
        ];
    }

    public function getDtoClassName(): string
    {
        return Github::class;
    }
}
