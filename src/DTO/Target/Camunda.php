<?php declare(strict_types=1);

namespace LinkORB\OrgSync\DTO\Target;

use LinkORB\OrgSync\DTO\Target;

class Camunda extends Target
{
    /** @var string|null */
    private $adminUsername;

    /** @var string|null */
    private $adminPassword;

    public function __construct(?string $adminPassword, ?string $adminUsername, string $baseUrl, string $name)
    {
        $this->adminPassword = $adminPassword;
        $this->adminUsername = $adminUsername;

        parent::__construct($baseUrl, $name);
    }

    public function getAdminUsername(): ?string
    {
        return $this->adminUsername;
    }

    public function getAdminPassword(): ?string
    {
        return $this->adminPassword;
    }
}