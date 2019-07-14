<?php declare(strict_types=1);

namespace LinkORB\OrgSync\DTO;

class Group
{
    /**
     * @var Group|null
     */
    private $parent;

    /**
     * @var string
     */
    private $displayName;

    /**
     * @var string|null
     */
    private $avatar;

    /**
     * @var User[]
     */
    private $members;

    /**
     * @var string[]
     */
    private $properties;

    /**
     * @var Target[]
     */
    private $targets;

    public function __construct(
        string $displayName,
        string $avatar = null,
        Group $parent = null,
        array $members = [],
        array $properties = [],
        array $targets = []
    )
    {
        $this->displayName = $displayName;
        $this->avatar = $avatar;
        $this->parent = $parent;
        $this->members = $members;
        $this->properties = $properties;
        $this->targets = $targets;
    }

    /**
     * @return Group|null
     */
    public function getParent(): ?Group
    {
        return $this->parent;
    }

    /**
     * @return string
     */
    public function getDisplayName(): string
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
     * @return User[]
     */
    public function getMembers(): array
    {
        return $this->members;
    }

    /**
     * @return string[]
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @return Target[]
     */
    public function getTargets(): array
    {
        return $this->targets;
    }
}
