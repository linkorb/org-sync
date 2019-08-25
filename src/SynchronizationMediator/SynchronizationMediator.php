<?php declare(strict_types=1);

namespace LinkORB\OrgSync\SynchronizationMediator;

use LinkORB\OrgSync\DTO\Target;
use LinkORB\OrgSync\Exception\SyncTargetException;
use LinkORB\OrgSync\Services\InputHandler;
use LinkORB\OrgSync\SynchronizationAdapter\AdapterFactory\AdapterFactoryInterface;
use LinkORB\OrgSync\SynchronizationAdapter\AdapterFactory\AdapterFactoryPoolInterface;
use LinkORB\OrgSync\DTO\Group;
use LinkORB\OrgSync\DTO\Organization;
use LinkORB\OrgSync\DTO\User;

class SynchronizationMediator implements SynchronizationMediatorInterface
{
    /** @var AdapterFactoryInterface */
    private $adapterFactory;

    /** @var AdapterFactoryPoolInterface */
    private $adapterFactoryPool;

    /** @var InputHandler */
    private $inputHandler;

    public function __construct(AdapterFactoryPoolInterface $adapterFactoryPool, InputHandler $inputHandler)
    {
        $this->adapterFactoryPool = $adapterFactoryPool;
        $this->inputHandler = $inputHandler;
    }

    /**
     * @param array $targets
     * @param array $organizations
     * @return Organization
     */
    public function initialize(array $targets, array $organizations): Organization
    {
        return $this->inputHandler->handle($targets, $organizations);
    }

    public function setTarget(Target $target): SynchronizationMediatorInterface
    {
        $this->adapterFactory = $this->adapterFactoryPool->get($target);

        return $this;
    }

    public function pushOrganization(Organization $organization): SynchronizationMediatorInterface
    {
        foreach ($this->inputHandler->getTargets() as $target) {
            $this->setTarget($target);

            $this->adapterFactory->createSyncRemover()->removeNonExists($organization);
        }

        foreach ($this->inputHandler->getTargets() as $target) {
            $this->setTarget($target);

            foreach ($organization->getUsers() as $user) {
                $this->pushUser($user);
            }
        }

        foreach ($organization->getGroups() as $group) {
            foreach ($group->getTargets() as $target) {
                $this->setTarget($target);

                $this->pushGroup($group);
            }
        }

        $this->adapterFactory = null;

        return $this;
    }

    public function pushGroup(Group $group): SynchronizationMediatorInterface
    {
        if ($this->adapterFactory->supports(Target::GROUP_PUSH)) {
            $this->adapterFactory->createGroupPushAdapter()->pushGroup($group);
        }

        return $this;
    }

    public function pushUser(User $user): SynchronizationMediatorInterface
    {
        if ($this->adapterFactory->supports(Target::USER_PUSH)) {
            $this->adapterFactory->createUserPushAdapter()->pushUser($user);
        }

        return $this;
    }

    public function setPassword(User $user, string $password): SynchronizationMediatorInterface
    {
        foreach ($this->inputHandler->getTargets() as $target) {
            $this->setTarget($target);

            if (!$this->adapterFactory->supports(Target::SET_PASSWORD)) {
                continue;
            }

            $this->adapterFactory->createSetPasswordAdapter()->setPassword($user, $password);
        }

        $this->adapterFactory = null;

        return $this;
    }

    public function pullOrganization(): Organization
    {
        if (!$this->adapterFactory->supports(Target::PULL_ORGANIZATION)) {
            throw new SyncTargetException(
                sprintf('Current target doesn\'t support \'%s\' action', Target::PULL_ORGANIZATION)
            );
        }

        return $this->adapterFactory->createOrganizationPullAdapter()->pullOrganization();
    }
}
