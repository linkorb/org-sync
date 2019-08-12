<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Services\SyncRemover;

use LinkORB\OrgSync\DTO\Organization;

interface SyncRemoverInterface
{
    public function removeNonExists(Organization $organization) :void;
}
