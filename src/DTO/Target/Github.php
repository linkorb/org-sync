<?php declare(strict_types=1);

namespace LinkORB\OrgSync\DTO\Target;

use LinkORB\OrgSync\DTO\Target;

class Github extends Target
{
    /**
     * @var string
     */
    private $token;

    public function __construct(string $baseUrl, string $name, string $token)
    {
        $this->token = $token;

        parent::__construct($baseUrl, $name);
    }

    public function getToken(): string
    {
        return $this->token;
    }
}
