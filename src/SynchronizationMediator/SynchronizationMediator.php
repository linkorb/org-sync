<?php declare(strict_types=1);

namespace LinkORB\OrgSync\SynchronizationMediator;

use LinkORB\OrgSync\AdapterFactory\AdapterFactoryInterface;
use LinkORB\OrgSync\AdapterFactory\AdapterFactoryPoolInterface;
use LinkORB\OrgSync\DTO\Group;
use LinkORB\OrgSync\DTO\Organization;
use LinkORB\OrgSync\DTO\User;

class SynchronizationMediator implements SynchronizationMediatorInterface
{
    /** @var AdapterFactoryInterface */
    private $adapterFactory;

    /** @var AdapterFactoryPoolInterface */
    private $adapterFactoryPool;

    public function __construct(AdapterFactoryPoolInterface $adapterFactoryPool)
    {
        $this->adapterFactoryPool = $adapterFactoryPool;
    }

    public function setAdapterFamily(string $adapterFamily): SynchronizationMediatorInterface
    {
        $this->adapterFactory = $this->adapterFactoryPool->get($adapterFamily);

        return $this;
    }

    public function pushOrganization(Organization $organization): SynchronizationMediatorInterface
    {
        $this->adapterFactory->createOrganizationPushAdapter()->push($organization);

        return $this;
    }

    public function pushGroup(Group $group): SynchronizationMediatorInterface
    {
        $this->adapterFactory->createGroupPushAdapter()->push($group);

        return $this;
    }

    public function pushUser(User $user): SynchronizationMediatorInterface
    {
        $this->adapterFactory->createUserPushAdapter()->push($user);

        return $this;
    }

    public function setPassword(User $user, string $password): SynchronizationMediatorInterface
    {
        $this->adapterFactory->createSetPasswordAdapter()->set($user, $password);

        return $this;
    }

    public function pullOrganization(): Organization
    {
        return $this->adapterFactory->createOrganizationPullAdapter()->pull();
    }
}
