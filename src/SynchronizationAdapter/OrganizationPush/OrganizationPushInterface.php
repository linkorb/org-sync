<?php declare(strict_types=1);

namespace LinkORB\OrgSync\SynchronizationAdapter\OrganizationPush;

use LinkORB\OrgSync\DTO\Organization;

interface OrganizationPushInterface
{
    public function pushOrganization(Organization $organization): self;
}
