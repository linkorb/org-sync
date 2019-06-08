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

    public function __construct(string $name, array $users = [], array $groups = [])
    {
        $this->name = $name;
        $this->users = $users;
        $this->groups = $groups;
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
}
