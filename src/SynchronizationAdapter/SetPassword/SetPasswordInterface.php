<?php declare(strict_types=1);

namespace LinkORB\OrgSync\SynchronizationAdapter\SetPassword;

use LinkORB\OrgSync\DTO\User;

interface SetPasswordInterface
{
    public function set(User $user, string $password): self;
}
