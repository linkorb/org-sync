<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Services;

use LinkORB\OrgSync\DTO\Group;
use LinkORB\OrgSync\DTO\Organization;
use LinkORB\OrgSync\DTO\Target;
use LinkORB\OrgSync\DTO\User;
use LinkORB\OrgSync\Services\Target\TargetPool;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class InputHandler
{
    public const GITHUB_ORGANIZATION = 'github_organization';

    /** @var TargetPool */
    private $targetsPool;

    /** @var DenormalizerInterface */
    private $denormalizer;

    public function __construct(TargetPool $targetsPool, DenormalizerInterface $denormalizer)
    {
        $this->targetsPool = $targetsPool;
        $this->denormalizer = $denormalizer;
    }

    public function handle(array $targets, array $organization): Organization
    {
        foreach ($targets as $targetName => $target) {
            $target['name'] = $targetName;

            $this->targetsPool->addTarget($target);
        }

        $groupsParents = [];
        $groupsMembers = [];
        $groupsTargets = [];
        foreach ($organization['groups'] ?? [] as $name => $group) {
            $groupsParents[$name] = $group['parent'] ?? null;
            $groupsMembers[$name] = $group['members'] ?? [];
            $groupsTargets[$name] = $group['targets'] ?? [];

            $group['parent'] = null;
            $group['members'] = [];
            $group['targets'] = [];

            $organization['groups'][$name] = $group;
        }

        /** @var Organization $organizationDto */
        $organizationDto = $this->denormalizer->denormalize(
            $organization,
            Organization::class,
            null,
            [
                'collection_id_name_map' => [
                    User::class => 'username',
                    Group::class => 'name',
                ],
            ]
        );

        foreach ($organizationDto->getGroups() as $group) {
            $group->addProperty(static::GITHUB_ORGANIZATION, $organizationDto->getName(), false);

            $this->handleGroupParents($groupsParents, $group, $organizationDto);
            $this->handleGroupMembers($groupsMembers, $group, $organizationDto);
            $this->handleGroupTargets($groupsTargets, $group);
        }

        return $organizationDto;
    }

    /**
     * @return Target[]
     */
    public function getTargets(): array
    {
        return $this->targetsPool->all();
    }

    private function handleGroupParents(array $groupsParents, Group $group, Organization $organizationDto): void
    {
        $parentGroupName = $groupsParents[$group->getName()];

        if (!empty($parentGroupName)) {
            $group->setParent(
                $organizationDto->getGroupByName($parentGroupName)
            );
        }
    }

    private function handleGroupMembers(array $groupsMembers, Group $group, Organization $organization): void
    {
        $groupMembers = $groupsMembers[$group->getName()];

        if (empty($groupMembers)) {
            foreach ($organization->getUsers() as $user) {
                $group->addMember($user);
            }

            return;
        }

        foreach ($groupMembers as $member) {
            $group->addMember(
                $organization->getUserByName($member)
            );
        }
    }

    private function handleGroupTargets(array $groupsTargets, Group $group): void
    {
        $groupTargets = $groupsTargets[$group->getName()];

        if (empty($groupTargets)) {
            foreach ($this->targetsPool->all() as $target) {
                $group->addTarget($target);
            }

            return;
        }

        foreach ($groupTargets as $target) {
            $group->addTarget(
                $this->targetsPool->get($target)
            );
        }
    }
}
