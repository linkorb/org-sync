<?php declare(strict_types=1);

namespace LinkORB\OrgSync\SynchronizationAdapter\AdapterFactory;

use BadMethodCallException;
use Github\Client;
use LinkORB\OrgSync\DTO\Target;
use LinkORB\OrgSync\Services\SyncRemover\GithubSyncRemover;
use LinkORB\OrgSync\Services\SyncRemover\SyncRemoverInterface;
use LinkORB\OrgSync\SynchronizationAdapter\GroupPush\GithubGroupPushAdapter;
use LinkORB\OrgSync\SynchronizationAdapter\GroupPush\GroupPushInterface;
use LinkORB\OrgSync\SynchronizationAdapter\OrganizationPull\OrganizationPullInterface;
use LinkORB\OrgSync\SynchronizationAdapter\SetPassword\SetPasswordInterface;
use LinkORB\OrgSync\SynchronizationAdapter\UserPush\UserPushInterface;

class GithubAdapterFactory implements AdapterFactoryInterface
{
    /**
     * @var Client
     */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function setTarget(Target $target): AdapterFactoryInterface
    {
        assert($target instanceof Target\Github);

        $this->client->authenticate($target->getToken(), Client::AUTH_HTTP_TOKEN);

        return $this;
    }

    public function createOrganizationPullAdapter(): OrganizationPullInterface
    {
        throw new BadMethodCallException('Not implemented yet');
    }

    public function createGroupPushAdapter(): GroupPushInterface
    {
        return new GithubGroupPushAdapter($this->client);
    }

    public function createUserPushAdapter(): UserPushInterface
    {
        throw new BadMethodCallException('Not implemented yet');
    }

    public function createSetPasswordAdapter(): SetPasswordInterface
    {
        throw new BadMethodCallException('Not implemented yet');
    }

    public function createSyncRemover(): SyncRemoverInterface
    {
        return new GithubSyncRemover($this->client);
    }

    public function supports(string $action): bool
    {
        return $action === Target::GROUP_PUSH;
    }
}
