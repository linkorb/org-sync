<?php declare(strict_types=1);

namespace LinkORB\OrgSync\DTO;

class Group
{
    /**
     * @var string
     */
    private $name;
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

    /**
     * Group constructor.
     * @param string $name
     * @param string $displayName
     * @param string|null $avatar
     * @param Group|null $parent
     * @param User[] $members
     * @param array $properties
     * @param array $targets
     */
    public function __construct(
        string $name,
        string $displayName,
        string $avatar = null,
        Group $parent = null,
        array $members = [],
        array $properties = [],
        array $targets = []
    )
    {
        $this->name = $name;
        $this->displayName = $displayName;
        $this->avatar = $avatar;
        $this->parent = $parent;
        $this->members = $members;
        $this->properties = $properties;
        $this->targets = $targets;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
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

    public function addProperty(string $key, string $value, bool $override = true): Group
    {
        $this->properties[$key] = ($override || !isset($this->properties[$key])) ? $value : $this->properties[$key];

        return $this;
    }

    /**
     * @return Target[]
     */
    public function getTargets(): array
    {
        return $this->targets;
    }

    public function setParent(?Group $parent): Group
    {
        $this->parent = $parent;

        return $this;
    }

    public function addMember(User $member): Group
    {
        $this->members[] = $member;

        return $this;
    }

    public function addTarget(Target $target): Group
    {
        $this->targets[] = $target;

        return $this;
    }
}
