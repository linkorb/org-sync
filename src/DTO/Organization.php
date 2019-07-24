<?php declare(strict_types=1);

namespace LinkORB\OrgSync\DTO;

use LinkORB\OrgSync\Exception\GroupSyncException;
use LinkORB\OrgSync\Exception\UserSyncException;

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
     * Organization constructor.
     * @param string $name
     * @param User[] $users
     * @param Group[] $groups
     */
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

    public function getGroupByName(string $name): Group
    {
        foreach ($this->groups as $group) {
            if ($group->getName() === $name) {
                return $group;
            }
        }

        throw new GroupSyncException('Linked group not found in organization');
    }

    public function getUserByName(string $name): User
    {
        foreach ($this->users as $user) {
            if ($user->getUsername() === $name) {
                return $user;
            }
        }

        throw new UserSyncException('Linked user not found in organization');
    }
}
