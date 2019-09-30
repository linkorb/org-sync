<?php declare(strict_types=1);

namespace LinkORB\OrgSync\SynchronizationAdapter\SetPassword;

use LinkORB\OrgSync\DTO\User;
use LinkORB\OrgSync\Services\Ldap\Client;
use LinkORB\OrgSync\Services\Ldap\LdapAssertionAwareTrait;
use LinkORB\OrgSync\SynchronizationAdapter\UserPush\LdapUserPushAdapter;

final class LdapSetPasswordAdapter implements SetPasswordInterface
{
    use LdapAssertionAwareTrait;

    /** @var Client */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function setPassword(User $user): SetPasswordInterface
    {
        $usersOrgUnit = $this->client->count(
            $this->client->search(sprintf('(ou=%s)', LdapUserPushAdapter::USERS_ORG_UNIT))
        );

        $this->assertResult($usersOrgUnit !== null && $usersOrgUnit > 0, 'Error during search!');

        $userSearchFirstDn = $this->client->getDn(
            $this->client->first(
                $this->client->search(
                    sprintf('(cn=%s)', $user->getUsername()),
                    ['ou' => LdapUserPushAdapter::USERS_ORG_UNIT]
                )
            )
        );

        $res = $this->client->modify(
            ['userPassword' => $this->encodePassword($user->getPassword())],
            $userSearchFirstDn
        );

        $this->assertResult((bool)$res, sprintf('User \'%s\' password wasn\'t changed', $user->getUsername()));

        return $this;
    }

    private function encodePassword(string $password): string
    {
        $salt = substr(str_shuffle(
            str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789', 4)
        ), 0, 4);

        return '{SSHA}' . base64_encode(sha1($password . $salt, true) . $salt);
    }
}
