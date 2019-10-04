<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Tests\Unit\Services\Ldap;

use LinkORB\OrgSync\DTO\Target\Ldap;
use LinkORB\OrgSync\Services\Ldap\Client;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    /**
     * @dataProvider getGenerateDnData
     */
    public function testGenerateDn(array $domain, array $rdn, string $expectedDn)
    {
        $ldap = new Ldap('', '', '', '', $domain);

        $client = $this->createPartialMock(Client::class, ['__destruct']);
        $client->__construct($ldap);

        $this->assertEquals($expectedDn, $client->generateDn($rdn));
    }

    /**
     * @return array
     */
    public function getGenerateDnData(): array
    {
        return [
            [
                ['internal', 'com'],
                ['cn' => ['test', 'somewhere'], 'ou' => 'org'],
                'cn=test,cn=somewhere,ou=org,dc=internal,dc=com'
            ],
            [
                ['com', 'internal'],
                ['cn' => ['somewhere', 'test']],
                'cn=somewhere,cn=test,dc=com,dc=internal'
            ],
            [
                [],
                ['uid' => 111222,'ou' => 'org'],
                'uid=111222,ou=org'
            ],
            [
                ['abc', 'xyz'],
                [],
                'dc=abc,dc=xyz'
            ],
            [
                [],
                [],
                ''
            ],
        ];
    }
}
