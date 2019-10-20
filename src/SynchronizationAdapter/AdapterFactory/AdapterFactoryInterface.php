<?php declare(strict_types=1);

namespace LinkORB\OrgSync\SynchronizationAdapter\AdapterFactory;

use LinkORB\OrgSync\DTO\Target;
use LinkORB\OrgSync\Services\SyncRemover\SyncRemoverInterface;
use LinkORB\OrgSync\SynchronizationAdapter\GroupPush\GroupPushInterface;
use LinkORB\OrgSync\SynchronizationAdapter\OrganizationPull\OrganizationPullInterface;
use LinkORB\OrgSync\SynchronizationAdapter\SetPassword\SetPasswordInterface;
use LinkORB\OrgSync\SynchronizationAdapter\UserPush\UserPushInterface;

interface AdapterFactoryInterface
{
    public function setTarget(Target $target): AdapterFactoryInterface;

    public function createGroupPushAdapter(): GroupPushInterface;

    public function createOrganizationPullAdapter(): OrganizationPullInterface;

    public function createSetPasswordAdapter(): SetPasswordInterface;

    public function createUserPushAdapter(): UserPushInterface;

    public function createSyncRemover(): SyncRemoverInterface;

    public function supports(string $action): bool;
}
