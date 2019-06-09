<?php declare(strict_types=1);

namespace LinkORB\OrgSync\AdapterFactory;

use LinkORB\OrgSync\SynchronizationAdapter\GroupPush\GroupPushInterface;
use LinkORB\OrgSync\SynchronizationAdapter\OrganizationPull\OrganizationPullInterface;
use LinkORB\OrgSync\SynchronizationAdapter\OrganizationPush\OrganizationPushInterface;
use LinkORB\OrgSync\SynchronizationAdapter\SetPassword\SetPasswordInterface;
use LinkORB\OrgSync\SynchronizationAdapter\UserPush\UserPushInterface;

interface AdapterFactoryInterface
{
    public function createGroupPushAdapter(): GroupPushInterface;

    public function createOrganizationPullAdapter(): OrganizationPullInterface;

    public function createOrganizationPushAdapter(): OrganizationPushInterface;

    public function createSetPasswordAdapter(): SetPasswordInterface;

    public function createUserPushAdapter(): UserPushInterface;
}
