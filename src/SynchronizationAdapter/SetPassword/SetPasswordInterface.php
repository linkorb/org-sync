<?php declare(strict_types=1);

namespace LinkORB\OrgSync\SynchronizationAdapter\SetPassword;

use LinkORB\OrgSync\DTO\User;

interface SetPasswordInterface
{
    public function setPassword(User $user): self;
}
