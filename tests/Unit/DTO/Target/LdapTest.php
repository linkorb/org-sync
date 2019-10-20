<?php

namespace LinkORB\OrgSync\Tests\Unit\DTO\Target;

use LinkORB\OrgSync\DTO\Target\Ldap;
use LinkORB\OrgSync\Tests\Helpers\AbstractGettersTestCase;

class LdapTest extends AbstractGettersTestCase
{
    public function gettersDataProvider(): array
    {
        return [
            ['bindRdn', ''],
            ['bindRdn', 'testBindRdn'],
            ['password', 'p@ss'],
            ['domain', ['long', 'long', 'domain', 'com']],
            ['domain', []],
        ];
    }

    public function getDefaultArgs(): array
    {
        return [
            'baseUrl' => '',
            'name' => '',
            'bindRdn' => '',
            'password' => '',
            'domain' => [],
        ];
    }

    public function getDtoClassName(): string
    {
        return Ldap::class;
    }
}
