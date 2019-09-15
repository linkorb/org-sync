<?php declare(strict_types=1);

namespace LinkORB\OrgSync\DTO\Target;

use LinkORB\OrgSync\DTO\Target;

final class Ldap extends Target
{
    /**
     * @var string;
     */
    private $bindRdn;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string[]
     */
    private $domain;

    public function __construct(
        string $baseUrl,
        string $name,
        string $usersBindRdn,
        string $password,
        array $domain
    ) {
        parent::__construct($baseUrl, $name);

        $this->bindRdn = $usersBindRdn;
        $this->password = $password;
        $this->domain = $domain;
    }

    public function getBindRdn(): string
    {
        return $this->bindRdn;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @return string[]
     */
    public function getDomain(): array
    {
        return $this->domain;
    }
}
