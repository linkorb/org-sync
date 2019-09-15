<?php declare(strict_types=1);

namespace LinkORB\OrgSync\SynchronizationAdapter\UserPush;

use LinkORB\OrgSync\DTO\User;
use LinkORB\OrgSync\Services\Ldap\Client;
use UnexpectedValueException;

class LdapUserPushAdapter implements UserPushInterface
{
    /** @var Client */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function pushUser(User $user): UserPushInterface
    {
        $userInfo = $this->getUserInfo($user);

        // TODO: Use ldap_add with LDAP_CONTROL_SYNC for PHP 7.3
        $userSearchCount = $this->client->count(
            $this->client->search(sprintf('(cn=%s)', $user->getUsername()))
        );

        if ($userSearchCount === null) {
            throw new UnexpectedValueException('Error during search!');
        }

        if ($userSearchCount === 0) {
            $res = $this->client->add($userInfo);
        } else {
            $res = $this->client->modify($userInfo);
        }

        if (!$res) {
            throw new UnexpectedValueException(sprintf('User \'%s\' wasn\'t added', $user->getUsername()));
        }

        return $this;
    }

    private function getUserInfo(User $user): array
    {
        $userInfo = [
            'cn' => $user->getUsername(),
            'sn' => $user->getProperties()['lastName'] ?? array_pop(explode(' ', (string) $user->getDisplayName())),
            'mail' => $user->getEmail(),
        ];

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
