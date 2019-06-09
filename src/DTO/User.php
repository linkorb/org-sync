<?php declare(strict_types=1);

namespace LinkORB\OrgSync\DTO;

class User
{
    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string|null
     */
    private $displayName;

    /**
     * @var string|null
     */
    private $avatar;

    /**
     * @var string[]
     */
    private $properties;

    public function __construct(
        string $username,
        string $email,
        string $displayName = null,
        string $avatar = null,
        array $properties = []
    )
    {
        $this->username = $username;
        $this->email = $email;
        $this->displayName = $displayName;
        $this->avatar = $avatar;
        $this->properties = $properties;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string|null
     */
    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    /**
     * @return string|null
     */
    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    /**
     * @return string[]
     */
    public function getProperties(): array
    {
        return $this->properties;
    }
}
