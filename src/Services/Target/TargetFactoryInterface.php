<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Services\Target;

use LinkORB\OrgSync\DTO\Target;

interface TargetFactoryInterface
{
    public function create(array $data): Target;
}