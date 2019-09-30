<?php declare(strict_types=1);

namespace LinkORB\OrgSync\DTO;

class User
{
    public const PREVIOUS_PASSWORD = 'previousPassword';

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

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

    /**
     * User constructor.
     * @param string $username
     * @param string|null $password
     * @param string|null $email
     * @param string|null $displayName
     * @param string|null $avatar
     * @param array $properties
     */
    public function __construct(
        string $username,
        string $password = null,
        string $email = null,
        string $displayName = null,
        string $avatar = null,
        array $properties = []
    )
    {
        $this->username = $username;
        $this->password = $password;
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
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

   public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

   public function setPreviousPassword(?string $password): self
    {
        if ($password !== null) {
            $this->properties[static::PREVIOUS_PASSWORD] = $password;
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
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
