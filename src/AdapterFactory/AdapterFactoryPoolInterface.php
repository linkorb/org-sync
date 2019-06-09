<?php declare(strict_types=1);

namespace LinkORB\OrgSync\AdapterFactory;

interface AdapterFactoryPoolInterface
{
    public function get(string $key): AdapterFactoryInterface;
}
