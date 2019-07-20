<?php declare(strict_types=1);

namespace LinkORB\OrgSync\DTO;

class Organization
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var User[]
     */
    private $users;

    /**
     * @var Group[]
     */
    private $groups;

    /**
     * @var Target[]
     */
    private $targets;

    public function __construct(string $name, array $users = [], array $groups = [], array $targets = [])
    {
        $this->name = $name;
        $this->users = $users;
        $this->groups = $groups;
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
     * @return User[]
     */
    public function getUsers(): array
    {
        return $this->users;
    }

    /**
     * @return Group[]
     */
    public function getGroups(): array
    {
        return $this->groups;
    }

    /**
     * @return Target[]
     */
    public function getTargets(): array
    {
        return $this->targets;
    }
}
