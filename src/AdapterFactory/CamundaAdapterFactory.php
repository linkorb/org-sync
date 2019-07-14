<?php declare(strict_types=1);

namespace LinkORB\OrgSync\AdapterFactory;

use GuzzleHttp\Client;
use LinkORB\OrgSync\SynchronizationAdapter\GroupPush\GroupPushInterface;
use LinkORB\OrgSync\SynchronizationAdapter\OrganizationPull\OrganizationPullInterface;
use LinkORB\OrgSync\SynchronizationAdapter\OrganizationPush\OrganizationPushInterface;
use LinkORB\OrgSync\SynchronizationAdapter\SetPassword\SetPasswordInterface;
use LinkORB\OrgSync\SynchronizationAdapter\UserPush\CamundaUserPushAdapter;
use LinkORB\OrgSync\SynchronizationAdapter\UserPush\UserPushInterface;

class CamundaAdapterFactory implements AdapterFactoryInterface
{
    public const ADAPTER_KEY = 'camunda';

    /** @var Client */
    private $camundaClient;

    public function __construct(string $baseUri, ?string $authUsername, ?string $authPassword)
    {
        $clientOptions = [
            'base_uri' => $baseUri,
        ];

        if ($authUsername && $authPassword) {
            $clientOptions['auth'] = [$authUsername, $authPassword];
        }

        $this->camundaClient = $this->getClient($clientOptions);
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
        return new CamundaUserPushAdapter($this->camundaClient);
    }

    public function createSetPasswordAdapter(): SetPasswordInterface
    {
        // TODO: Implement createSetPasswordAdapter() method.
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
}
