<?php

namespace LinkORB\OrgSync\Tests\Unit\DTO;

use LinkORB\OrgSync\DTO\User;
use LinkORB\OrgSync\Tests\Helpers\AbstractGettersTestCase;

class UserTest extends AbstractGettersTestCase
{
    public function gettersDataProvider(): array
    {
        return [
            ['username', 'test222'],
            ['username', 'qwerty'],
            ['password', '123Qwe'],
            ['password', null],
            ['email', 'a@a.com'],
            ['email', 'test@test'],
            ['displayName', null],
            ['displayName', '1234'],
            ['avatar', '1.gif'],
            ['avatar', null],
            ['properties', []],
            ['properties', ['1' => 77, '4' => 54]],
            ['properties', ['a77', 'b54']],
        ];
    }

    public function getDefaultArgs(): array
    {
        return [
            'username' => 'temp123',
            'password' => '123456',
            'email' => 'example@test.com',
            'displayName' => null,
            'avatar' => null,
            'properties' => [],
        ];
    }

    public function getDtoClassName(): string
    {
        return User::class;
    }

    public function testSetPassword()
    {
        $somePass = '0000000';

        $dto = (new User('user1', 'differentPass'))->setPassword($somePass);

        $this->assertSame($dto->getPassword(), $somePass);
    }

    public function testSetPreviousPassword()
    {
        $somePass = '0000000';

        $dto = (new User('user1', 'differentPass'))->setPreviousPassword($somePass);

        $this->assertSame($dto->getProperties()[User::PREVIOUS_PASSWORD], $somePass);
    }
}
