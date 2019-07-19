<?php declare(strict_types=1);

namespace LinkORB\OrgSync\SynchronizationAdapter\OrganizationPush;

use LinkORB\OrgSync\DTO\Organization;

final class CamundaOrganizationPushAdapter implements OrganizationPushInterface
{
    public function pushOrganization(Organization $organization): OrganizationPushInterface
    {
        // Camunda has no organizations
        return $this;
    }
}