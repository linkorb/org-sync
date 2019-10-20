<?php declare(strict_types=1);

namespace LinkORB\OrgSync\DTO\Target;

use LinkORB\OrgSync\DTO\Target;

class Mattermost extends Target
{
    /** @var string */
    private $token;

    /** @var string */
    private $login;

    /** @var string */
    private $password;

    /** @var string */
    private $scheme;

    public function __construct(
        string $baseUrl,
        string $name,
        string $token = null,
        string $login = null,
        string $password = null
    ) {
        parent::__construct($baseUrl, $name);

        $urlParts = explode('://', $this->getBaseUrl(), 2);

        $this->baseUrl = end($urlParts);
        $this->scheme = reset($urlParts);

        $this->token = $token;
        $this->login = $login;
        $this->password = $password;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getScheme(): string
    {
        return $this->scheme;
    }
}
