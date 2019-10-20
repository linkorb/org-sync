<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Services\Ldap;

use UnexpectedValueException;

trait LdapAssertionAwareTrait
{
    private function assertResult(bool $condition, string $exceptionMessage): void
    {
        if (!$condition) {
            throw new UnexpectedValueException($exceptionMessage);
        }
    }
}
