<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Services\Ldap;

use LinkORB\OrgSync\DTO\Group;

class LdapParentHelper
{
    use LdapAssertionAwareTrait;

    public function getParentGroups(array $parents, Group $group): array
    {
        if ($group->getParent() === null) {
            return $parents;
        } else {
            // to prevent circular reference
            $this->assertResult(!in_array($group->getParent()->getName(), $parents), 'Circular reference detected');

            $parents[] = $group->getParent()->getName();

            return $this->getParentGroups($parents, $group->getParent());
        }
    }
}
