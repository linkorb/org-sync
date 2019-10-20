<?php declare(strict_types=1);

namespace LinkORB\OrgSync\SynchronizationAdapter\UserPush;

use LinkORB\OrgSync\DTO\User;
use LinkORB\OrgSync\Services\Ldap\Client;
use LinkORB\OrgSync\Services\Ldap\LdapAssertionAwareTrait;
use LinkORB\OrgSync\Services\Ldap\UserDataMapper;

class LdapUserPushAdapter implements UserPushInterface
{
    public const USERS_ORG_UNIT = 'users';

    use LdapAssertionAwareTrait;

    /** @var Client */
    private $client;

    /** @var UserDataMapper */
    private $mapper;

    public function __construct(Client $client, UserDataMapper $mapper)
    {
        $this->client = $client;
        $this->mapper = $mapper;
    }

    public function pushUser(User $user): UserPushInterface
    {
        $userInfo = $this->mapper->map($user);

        $usersOrgUnit = $this->client->count(
            $this->client->search(sprintf('(ou=%s)', static::USERS_ORG_UNIT))
        );

        $this->assertResult($usersOrgUnit !== null, 'Error during search!');
        if ($usersOrgUnit === 0) {
            $this->client->add(
                [
                    'ou' => static::USERS_ORG_UNIT,
                    'objectClass' => ['top', 'organizationalUnit'],
                ],
                ['ou' => static::USERS_ORG_UNIT]
            );
        }

        $userFirst = $this->client->first(
            $this->client->search(sprintf('(cn=%s)', $user->getUsername()), ['ou' => static::USERS_ORG_UNIT])
        );

        if (!$userFirst) {
            $res = $this->client->add($userInfo, ['uid' => $user->getUsername(), 'ou' => static::USERS_ORG_UNIT]);
        } else {
            $res = $this->client->modify($userInfo, $this->client->getDn($userFirst));
        }

        $this->assertResult((bool)$res, sprintf('User \'%s\' wasn\'t pushed', $user->getUsername()));

        return $this;
    }
}
