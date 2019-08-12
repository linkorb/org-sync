<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Services\Camunda;

use LinkORB\OrgSync\DTO\Group;

class CamundaGroupMapper
{
    public function map(array $data)
    {
        return new Group($data['id'], $data['name']);
    }
}
