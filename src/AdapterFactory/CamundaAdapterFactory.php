<?php declare(strict_types=1);

namespace LinkORB\OrgSync\AdapterFactory;

use GuzzleHttp\Client;
use LinkORB\OrgSync\Services\Camunda\ResponseChecker;
use LinkORB\OrgSync\Services\PasswordHelper;
use LinkORB\OrgSync\SynchronizationAdapter\GroupPush\GroupPushInterface;
use LinkORB\OrgSync\SynchronizationAdapter\OrganizationPull\OrganizationPullInterface;
use LinkORB\OrgSync\SynchronizationAdapter\OrganizationPush\OrganizationPushInterface;
use LinkORB\OrgSync\SynchronizationAdapter\SetPassword\CamundaSetPasswordAdapter;
use LinkORB\OrgSync\SynchronizationAdapter\SetPassword\SetPasswordInterface;
use LinkORB\OrgSync\SynchronizationAdapter\UserPush\CamundaUserPushAdapter;
use LinkORB\OrgSync\SynchronizationAdapter\UserPush\UserPushInterface;

class CamundaAdapterFactory implements AdapterFactoryInterface
{
    public const ADAPTER_KEY = 'camunda';

    /** @var Client */
    private $camundaClient;

    /** @var PasswordHelper */
    private $passwordHelper;

    public function __construct(string $baseUri, ?string $authUsername, ?string $authPassword, ?string $defaultPassSalt)
    {
        $clientOptions = [
            'base_uri' => $baseUri,
        ];

        if ($authUsername && $authPassword) {
            $clientOptions['auth'] = [$authUsername, $authPassword];
        }

        $this->camundaClient = $this->getClient($clientOptions);
        $this->passwordHelper = $this->getPasswordHelper($defaultPassSalt);
    }

    public function createOrganizationPullAdapter(): OrganizationPullInterface
    {
        // TODO: Implement createOrganizationPullAdapter() method.
    }

    public function createGroupPushAdapter(): GroupPushInterface
    {
        // TODO: Implement createGroupPushAdapter() method.
    }

    public function createUserPushAdapter(): UserPushInterface
    {
        return new CamundaUserPushAdapter($this->camundaClient, $this->passwordHelper, new ResponseChecker());
    }

    public function createSetPasswordAdapter(): SetPasswordInterface
    {
        return new CamundaSetPasswordAdapter($this->camundaClient, $this->passwordHelper, new ResponseChecker());
    }

    public function createOrganizationPushAdapter(): OrganizationPushInterface
    {
        // TODO: Implement createOrganizationPushAdapter() method.
    }

    protected function getClient(array $options): Client
    {
        if ($this->camundaClient) {
            return $this->camundaClient;
        }

        return new Client($options);
    }

    protected function getPasswordHelper(?string $salt): PasswordHelper
    {
        if ($this->passwordHelper) {
            return $this->passwordHelper;
        }

        return new PasswordHelper($salt);
    }
}
