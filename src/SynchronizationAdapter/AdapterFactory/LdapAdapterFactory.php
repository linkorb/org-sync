<?php declare(strict_types=1);

namespace LinkORB\OrgSync\SynchronizationAdapter\AdapterFactory;

use LinkORB\OrgSync\DTO\Target;
use LinkORB\OrgSync\Services\Ldap\Client;
use LinkORB\OrgSync\Services\Ldap\LdapParentHelper;
use LinkORB\OrgSync\Services\Ldap\UserDataMapper;
use LinkORB\OrgSync\Services\SyncRemover\LdapSyncRemover;
use LinkORB\OrgSync\Services\SyncRemover\SyncRemoverInterface;
use LinkORB\OrgSync\SynchronizationAdapter\GroupPush\GroupPushInterface;
use LinkORB\OrgSync\SynchronizationAdapter\GroupPush\LdapGroupPushAdapter;
use LinkORB\OrgSync\SynchronizationAdapter\OrganizationPull\OrganizationPullInterface;
use LinkORB\OrgSync\SynchronizationAdapter\SetPassword\SetPasswordInterface;
use LinkORB\OrgSync\SynchronizationAdapter\UserPush\LdapUserPushAdapter;
use LinkORB\OrgSync\SynchronizationAdapter\UserPush\UserPushInterface;

class LdapAdapterFactory implements AdapterFactoryInterface
{
    /** @var Client */
    private $client;

    /** @var UserDataMapper */
    private $userMapper;

    /** @var LdapParentHelper */
    private $parentHelper;

    public function __construct()
    {
        $this->userMapper = new UserDataMapper();
        $this->parentHelper = new LdapParentHelper();
    }

    public function createOrganizationPullAdapter(): OrganizationPullInterface
    {
        // TODO: Implement createOrganizationPullAdapter() method.
    }

    public function createGroupPushAdapter(): GroupPushInterface
    {
        return new LdapGroupPushAdapter($this->client, $this->userMapper, $this->parentHelper);
    }

    public function createUserPushAdapter(): UserPushInterface
    {
        return new LdapUserPushAdapter($this->client,$this->userMapper);
    }

    public function createSetPasswordAdapter(): SetPasswordInterface
    {
        // TODO: Implement createSetPasswordAdapter() method.
    }

    public function setTarget(Target $target): AdapterFactoryInterface
    {
        $this->client = $this->getClient($target);
        $this->client
            ->init()
            ->bind();

        return $this;
    }

    public function createSyncRemover(): SyncRemoverInterface
    {
        return new LdapSyncRemover($this->client, $this->parentHelper);
    }

    public function supports(string $action): bool
    {
        return in_array($action, [
            Target::GROUP_PUSH,
            Target::SET_PASSWORD,
            Target::USER_PUSH,
        ], true);
    }

    protected function getClient(Target\Ldap $target): Client
    {
        return new Client($target);
    }
}
