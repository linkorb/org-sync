<?php declare(strict_types=1);

namespace LinkORB\OrgSync\SynchronizationAdapter\OrganizationPull;

use LinkORB\OrgSync\DTO\Organization;

interface OrganizationPullInterface
{
    public function pullOrganization(): Organization;
}
