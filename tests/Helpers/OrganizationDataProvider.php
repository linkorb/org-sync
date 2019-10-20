<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Tests\Helpers;

use LinkORB\OrgSync\DTO\Group;
use LinkORB\OrgSync\DTO\Organization;
use LinkORB\OrgSync\DTO\Target;
use LinkORB\OrgSync\DTO\Target\Camunda;
use LinkORB\OrgSync\DTO\User;
use LinkORB\OrgSync\Services\InputHandler;

class OrganizationDataProvider
{
    public static function provideDto(): Organization
    {
        list($targets, $organization) = static::provideArray();

        $targetDtos = OrganizationDataProvider::transformToTargets($targets);
        $userDtos = OrganizationDataProvider::transformToUsers($organization['users']);
        $groups = OrganizationDataProvider::transformToGroups($organization['groups'], $userDtos, $targetDtos);

        return new Organization($organization['name'], $userDtos, $groups);
    }

    public static function provideArray(): array
    {
        return [
            [
                'camunda1' => [
                    'type' => 'camunda',
                    'adminPassword' => 'password',
                    'adminUsername' => 'odmen',
                    'baseUrl' => 'http://test/admin',
                ],
                'camunda2' => [
                    'type' => 'camunda',
                    'adminPassword' => '123Qwe',
                    'adminUsername' => 'john',
                    'baseUrl' => 'http://127.0.0.1',
                ],
            ],
            [
                'name' => 'LinkORB',
                'users' => [
                    'jfaassen' => [
                        'email' => 'j.faassen@linkorb.com',
                        'displayName' => 'Joost Faassen',
                        'avatar' => 'https://example.com/joost.gif',
                        'properties' => [
                            'githubId' => 'joostfaassen',
                            'skypeId' => 'j.faassen'
                        ]
                    ],
                    'joe' => [
                        'email' => 'joe@example.com',
                    ],
                    'temp' => [
                        'email' => 'example@temp.com',
                    ]
                ],
                'groups' => [
                    'team' => [
                        'displayName' => 'the whole team',
                    ],
                    'developers' => [
                        'parent' => 'team',
                        'displayName' => 'Developers',
                        'avatar' => 'https://example.com/devs.png',
                        'members' => [
                            'jfaassen',
                            'joe',
                        ],
                        'properties' => [
                            'hello' => 'world'
                        ],
                        'targets' => [
                            'camunda1',
                            'camunda2',
                        ]
                    ]
                ],
            ],
        ];
    }

    /**
     * @param array $usersArray
     * @return User[]
     */
    public static function transformToUsers(array $usersArray): array
    {
        $users = [];
        foreach ($usersArray as $username => $user) {
            $users[] = new User(
                $username,
                null,
                $user['email'],
                $user['displayName'],
                $user['avatar'],
                $user['properties'] ?? []
            );
        }

        return $users;
    }

    /**
     * @param array $targetsArray
     * @return Target[]
     */
    public static function transformToTargets(array $targetsArray): array
    {
        $targets = [];
        foreach ($targetsArray as $name => $target) {
            $targets[$name] = new Camunda(
                $target['baseUrl'],
                $name,
                $target['adminPassword'],
                $target['adminUsername']
            );
        }

        return $targets;
    }

    /**
     * @param array $groupsArray
     * @param User[] $users
     * @param Target[] $targets
     * @return Group[]
     */
    public static function transformToGroups(
        array $groupsArray,
        array $users,
        array $targets,
        string $orgName = null
    ): array
    {
        /** @var Group[] $groups */
        $groups = [];
        $groupParents = [];
        foreach ($groupsArray as $name => $group) {
            $props = $group['properties'] ?? [];
            if ($orgName) {
                $props[InputHandler::GITHUB_ORGANIZATION] = $orgName;
            }

            $groups[] = new Group(
                $name,
                $group['displayName'],
                $group['avatar'],
                null,
                [],
                $props
            );

            foreach ($group['targets'] ?? [] as $linkedTarget) {
                foreach ($targets as $target) {
                    if ($target->getName() === $linkedTarget) {
                        end($groups)->addTarget($target);

                        break;
                    }
                }
            }

            if (empty($group['targets'])) {
                foreach ($targets as $target) {
                    end($groups)->addTarget($target);
                }
            }

            foreach ($group['members'] ?? [] as $linkedMember) {
                foreach ($users as $user) {
                    if ($user->getUsername() === $linkedMember) {
                        end($groups)->addMember($user);

                        break;
                    }
                }
            }

            if (empty($group['members'])) {
                foreach ($users as $user) {
                    end($groups)->addMember($user);
                }
            }

            $groupParents[$name] = $group['parent'] ?? null;
        }

        for ($i = 0; $i < count($groups); ++$i) {
            if (empty($groupParents[$groups[$i]->getName()])) {
                continue;
            }

            for ($j = 0; $j < count($groups); ++$j) {
                if ($groups[$j]->getName() === $groupParents[$groups[$i]->getName()]) {
                    $groups[$i]->setParent($groups[$j]);

                    continue 2;
                }
            }
        }

        return $groups;
    }
}
