<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Tests\Unit\Services\Ldap;

use LinkORB\OrgSync\DTO\User;
use LinkORB\OrgSync\Services\Ldap\UserDataMapper;
use PHPUnit\Framework\TestCase;

class UserDataMapperTest extends TestCase
{
    /** @var UserDataMapper */
    private $mapper;

    protected function setUp(): void
    {
        $this->mapper = new UserDataMapper();
    }

    /**
     * @dataProvider getMapData
     */
    public function testMap(User $user, array $expectedUserData)
    {
        $this->assertEquals($expectedUserData, $this->mapper->map($user));
    }

    public function getMapData(): array
    {
        return [
            [
                new User(''),
                [
                    'cn' => '',
                    'uid' => '',
                    'sn' => '',
                    'objectClass' => ['inetOrgPerson', 'organizationalPerson', 'person', 'top']
                ],
            ],
            [
                new User('uadmin', 'p@ss', 'admin@example.com', 'superadm', '1.jpg', ['lastName' => 'Jong']),
                [
                    'cn' => 'uadmin',
                    'uid' => 'uadmin',
                    'sn' => 'Jong',
                    'objectClass' => ['inetOrgPerson', 'organizationalPerson', 'person', 'top'],
                    'mail' => 'admin@example.com',
                    'displayName' => 'superadm',
                    'Photo' => '1.jpg',
                ],
            ],
            [
                new User('uadmin', 'p@ss', '', 'superadm'),
                [
                    'cn' => 'uadmin',
                    'uid' => 'uadmin',
                    'sn' => 'superadm',
                    'objectClass' => ['inetOrgPerson', 'organizationalPerson', 'person', 'top'],
                    'displayName' => 'superadm',
                ],
            ],
        ];
    }
}
