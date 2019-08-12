<?php declare(strict_types=1);

namespace LinkORB\OrgSync\SynchronizationAdapter\AdapterFactory;

use BadMethodCallException;
use GuzzleHttp\Client;
use LinkORB\OrgSync\DTO\Group;
use LinkORB\OrgSync\DTO\Target;
use LinkORB\OrgSync\DTO\User;
use LinkORB\OrgSync\Services\Camunda\CamundaGroupMapper;
use LinkORB\OrgSync\Services\Camunda\CamundaGroupMemberProvider;
use LinkORB\OrgSync\Services\Camunda\CamundaGroupProvider;
use LinkORB\OrgSync\Services\Camunda\CamundaUserMapper;
use LinkORB\OrgSync\Services\Camunda\CamundaUserProvider;
use LinkORB\OrgSync\Services\Camunda\ResponseChecker;
use LinkORB\OrgSync\Services\PasswordHelper;
use LinkORB\OrgSync\Services\SyncRemover\CamundaSyncRemover;
use LinkORB\OrgSync\Services\SyncRemover\SyncRemoverInterface;
use LinkORB\OrgSync\SynchronizationAdapter\GroupPush\CamundaGroupPushAdapter;
use LinkORB\OrgSync\SynchronizationAdapter\GroupPush\GroupPushInterface;
use LinkORB\OrgSync\SynchronizationAdapter\OrganizationPull\OrganizationPullInterface;
use LinkORB\OrgSync\SynchronizationAdapter\OrganizationPush\CamundaOrganizationPushAdapter;
use LinkORB\OrgSync\SynchronizationAdapter\OrganizationPush\OrganizationPushInterface;
use LinkORB\OrgSync\SynchronizationAdapter\SetPassword\CamundaSetPasswordAdapter;
use LinkORB\OrgSync\SynchronizationAdapter\SetPassword\SetPasswordInterface;
use LinkORB\OrgSync\SynchronizationAdapter\UserPush\CamundaUserPushAdapter;
use LinkORB\OrgSync\SynchronizationAdapter\UserPush\UserPushInterface;

class CamundaAdapterFactory implements AdapterFactoryInterface
{
    /** @var Client */
    private $camundaClient;

    /** @var PasswordHelper */
    private $passwordHelper;

    /** @var string|null */
    private $defaultPassSalt;

    public function __construct(?string $defaultPassSalt)
    {
        $this->defaultPassSalt = $defaultPassSalt;
    }

    public function setTarget(Target $target): AdapterFactoryInterface
    {
        assert($target instanceof Target\Camunda);

        $clientOptions = [
            'base_uri' => $target->getBaseUrl(),
            'exceptions' => false,
        ];

        if ($target->getAdminUsername() && $target->getAdminPassword()) {
            $clientOptions['auth'] = [$target->getAdminUsername(), $target->getAdminPassword()];
        }

        $this->camundaClient = $this->getClient($clientOptions);
        $this->passwordHelper = $this->getPasswordHelper($this->defaultPassSalt . $target->getName());

        return $this;
    }

    public function createOrganizationPullAdapter(): OrganizationPullInterface
    {
        throw new BadMethodCallException('Not implemented yet');
    }

    public function createGroupPushAdapter(): GroupPushInterface
    {
        return new CamundaGroupPushAdapter($this->camundaClient, new ResponseChecker(Group::class));
    }

    public function createUserPushAdapter(): UserPushInterface
    {
        return new CamundaUserPushAdapter(
            $this->camundaClient,
            $this->passwordHelper,
            new ResponseChecker(User::class)
        );
    }

    public function createSetPasswordAdapter(): SetPasswordInterface
    {
        return new CamundaSetPasswordAdapter(
            $this->camundaClient,
            $this->passwordHelper,
            new ResponseChecker(User::class)
        );
    }

    public function createOrganizationPushAdapter(): OrganizationPushInterface
    {
        return new CamundaOrganizationPushAdapter();
    }

    public function createSyncRemover(): SyncRemoverInterface
    {
        $userProvider = new CamundaUserProvider($this->camundaClient, new CamundaUserMapper());
        $groupMapper = new CamundaGroupMapper();
        $groupProvider = new CamundaGroupProvider($this->camundaClient, $groupMapper);
        $userGroupProvider = new CamundaGroupMemberProvider($this->camundaClient, $groupMapper);

        return new CamundaSyncRemover($userProvider, $groupProvider, $userGroupProvider, $this->camundaClient);
    }

    protected function getClient(array $options): Client
    {
        return new Client($options);
    }

    protected function getPasswordHelper(?string $salt): PasswordHelper
    {
        return new PasswordHelper($salt);
    }
}
