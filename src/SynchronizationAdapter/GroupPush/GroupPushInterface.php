<?php declare(strict_types=1);

namespace LinkORB\OrgSync\SynchronizationAdapter\GroupPush;

use LinkORB\OrgSync\DTO\Group;

interface GroupPushInterface
{
    public function push(Group $group);
}
