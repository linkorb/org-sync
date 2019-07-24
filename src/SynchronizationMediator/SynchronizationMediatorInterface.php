<?php declare(strict_types=1);

namespace LinkORB\OrgSync\SynchronizationMediator;

use LinkORB\OrgSync\DTO\Group;
use LinkORB\OrgSync\DTO\Organization;
use LinkORB\OrgSync\DTO\Target;
use LinkORB\OrgSync\DTO\User;

interface SynchronizationMediatorInterface
{
    public function pushOrganization(Organization $organization): self;

    public function pushGroup(Group $organization): self;

    public function pushUser(User $user): self;

    public function setPassword(User $user, string $password): self;

    public function pullOrganization(): Organization;

    public function setTarget(Target $target): SynchronizationMediatorInterface;

    public function initialize(array $targets, array $organizations): Organization;
}
