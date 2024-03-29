<?php declare(strict_types=1);

namespace LinkORB\OrgSync\SynchronizationAdapter\UserPush;

use LinkORB\OrgSync\DTO\User;

interface UserPushInterface
{
    public function pushUser(User $user): self;
}
