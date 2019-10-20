<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Services\Ldap;

use LinkORB\OrgSync\DTO\User;

class UserDataMapper
{
    public function map(User $user): array
    {
        $userInfo = [
            'cn' => $user->getUsername(),
            'uid' => $user->getUsername(),
            'sn' => $user->getProperties()[User::LAST_NAME]
                ?? array_pop(explode(' ', (string) $user->getDisplayName())),
        ];

        if ($user->getEmail()) {
            $userInfo['mail'] = $user->getEmail();
        }

        if ($user->getDisplayName()) {
            $userInfo['displayName'] = $user->getDisplayName();
        }

        if ($user->getAvatar()) {
            $userInfo['Photo'] = $user->getAvatar();
        }

        $userInfo['objectClass'] = ['inetOrgPerson', 'organizationalPerson', 'person', 'top'];

        return $userInfo;
    }
}
