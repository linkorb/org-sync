<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Services\Camunda;

use LinkORB\OrgSync\DTO\User;

final class CamundaUserMapper
{
    public function map(array $data): User
    {
        return new User(
            $data['id'],
            null,
            $data['email'] ?? null,
            null,
            null,
            [
                User::FIRST_NAME => $data['firstName'] ?? null,
                User::LAST_NAME => $data['lastName'] ?? null,
            ]
        );
    }
}
