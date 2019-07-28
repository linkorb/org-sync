<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Tests\Unit\Services;

use Doctrine\Common\Annotations\AnnotationReader;
use LinkORB\OrgSync\DTO\Group;
use LinkORB\OrgSync\DTO\Organization;
use LinkORB\OrgSync\DTO\Target;
use LinkORB\OrgSync\DTO\User;
use LinkORB\OrgSync\Services\Denormalizer\AssociativeArrayDenormalizer;
use LinkORB\OrgSync\Services\InputHandler;
use LinkORB\OrgSync\Services\Target\TargetPool;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class InputHandlerTest extends TestCase
{
    /** @var InputHandler */
    private $inputHandler;

    protected function setUp(): void
    {
        $metadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $normalizer = new ObjectNormalizer(
            $metadataFactory,
            null,
            null,
            new PropertyInfoExtractor([], [new PhpDocExtractor(), new ReflectionExtractor()])
        );
        $serializer = new Serializer([$normalizer, new AssociativeArrayDenormalizer()]);

        $this->inputHandler = new InputHandler(new TargetPool($serializer), $serializer);

        parent::setUp();
    }

    /**
     * @dataProvider getHandleData
     */
    public function testDenormalization(array $targets, array $organization)
    {
        /** @var Target[] $targets */
        $targetDtos = $this->getTargets($targets);
        /** @var User[] $users */
        $userDtos = $this->getUsers($organization);
        $groups = $this->getOrgGroups($organization, $userDtos, $targetDtos);

        $this->assertEquals(
            new Organization($organization['name'], $userDtos, $groups),
            $this->inputHandler->handle($targets, $organization)
        );
        $this->assertEquals($targetDtos, $this->inputHandler->getTargets());
    }

    // TODO: add more data
    public function getHandleData(): array
    {
        return [
            [
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
            ],
        ];
    }

    private function getUsers(array $organization): array
    {
        $users = [];
        foreach ($organization['users'] as $username => $user) {
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

    private function getTargets(array $targetsArray): array
    {
        $targets = [];
        foreach ($targetsArray as $name => $target) {
            $targets[$name] = new Target\Camunda(
                $target['baseUrl'],
                $name,
                $target['adminPassword'],
                $target['adminUsername']
            );
        }

        return $targets;
    }

    /**
     * @param array $organization
     * @param User[] $users
     * @param Target[] $targets
     * @return Group[]
     */
    private function getOrgGroups(array $organization, array $users, array $targets): array
    {
        /** @var Group[] $groups */
        $groups = [];
        $groupParents = [];
        foreach ($organization['groups'] as $name => $group) {
            $groups[] = new Group(
                $name,
                $group['displayName'],
                $group['avatar'],
                null,
                [],
                $group['properties'] ?? []
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
