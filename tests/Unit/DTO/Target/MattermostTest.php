<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Tests\Unit\DTO\Target;

use LinkORB\OrgSync\DTO\Target\Mattermost;
use LinkORB\OrgSync\Tests\Helpers\AbstractGettersTestCase;

class MattermostTest extends AbstractGettersTestCase
{
    public function gettersDataProvider(): array
    {
        return [
            ['token', null],
            ['token', 'token11'],
            ['login', null],
            ['login', 'a@a.com'],
            ['password', null],
            ['password', 'p@ss'],
        ];
    }

    public function getDefaultArgs(): array
    {
        return [
            'baseUrl' => '',
            'name' => '',
            'token' => '',
            'login' => '',
            'password' => '',
        ];
    }

    public function getDtoClassName(): string
    {
        return Mattermost::class;
    }

    public function testScheme()
    {
        $dto = new Mattermost('http://matterhost.com/test', 'test');

        $this->assertSame('http', $dto->getScheme());
        $this->assertSame('matterhost.com/test', $dto->getBaseUrl());
    }
}
