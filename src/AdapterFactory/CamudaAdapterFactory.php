<?php declare(strict_types=1);

namespace LinkORB\OrgSync\AdapterFactory;

use LinkORB\OrgSync\SynchronizationAdapter\GroupPush\GroupPushInterface;
use LinkORB\OrgSync\SynchronizationAdapter\OrganizationPull\OrganizationPullInterface;
use LinkORB\OrgSync\SynchronizationAdapter\OrganizationPush\OrganizationPushInterface;
use LinkORB\OrgSync\SynchronizationAdapter\SetPassword\SetPasswordInterface;
use LinkORB\OrgSync\SynchronizationAdapter\UserPush\UserPushInterface;

class CamudaAdapterFactory implements AdapterFactoryInterface
{
    public const ADAPTER_KEY = 'camunda';

    public function createOrganizationPullAdapter(): OrganizationPullInterface
    {
        // TODO: Implement createOrganizationPullAdapter() method.
    }

    public function createGroupPushAdapter(): GroupPushInterface
    {
        // TODO: Implement createGroupPushAdapter() method.
    }

    public function createUserPushAdapter(): UserPushInterface
    {
        // TODO: Implement createUserPushAdapter() method.
    }

    public function createSetPasswordAdapter(): SetPasswordInterface
    {
        // TODO: Implement createSetPasswordAdapter() method.
    }

    public function createOrganizationPushAdapter(): OrganizationPushInterface
    {
        // TODO: Implement createOrganizationPushAdapter() method.
    }
}
