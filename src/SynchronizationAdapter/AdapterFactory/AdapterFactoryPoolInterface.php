<?php declare(strict_types=1);

namespace LinkORB\OrgSync\SynchronizationAdapter\AdapterFactory;

use LinkORB\OrgSync\DTO\Target;

interface AdapterFactoryPoolInterface
{
    public function get(Target $target): AdapterFactoryInterface;
}
