<?php declare(strict_types=1);

namespace LinkORB\OrgSync\SynchronizationAdapter\UserPush;

use LinkORB\OrgSync\DTO\User;

trait CreateUpdateUserAwareTrait
{
    public function doPush(User $user): void
    {
        if (!$this->exists($user)) {
            $this->create($user);
        } else {
            $this->update($user);
        }
    }

    abstract protected function exists(User $user): bool;

    abstract protected function update(User $user): void;

    abstract protected function create(User $user): void;
}
