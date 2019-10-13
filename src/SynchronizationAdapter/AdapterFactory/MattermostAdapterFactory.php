<?php declare(strict_types=1);

namespace LinkORB\OrgSync\SynchronizationAdapter\AdapterFactory;

use BadMethodCallException;
use Gnello\Mattermost\Driver;
use LinkORB\OrgSync\DTO\Target;
use LinkORB\OrgSync\DTO\Target\Mattermost;
use LinkORB\OrgSync\DTO\User;
use LinkORB\OrgSync\Services\Camunda\ResponseChecker;
use LinkORB\OrgSync\Services\Mattermost\BaseEntriesProvider;
use LinkORB\OrgSync\Services\PasswordHelper;
use LinkORB\OrgSync\Services\SyncRemover\MattermostSyncRemover;
use LinkORB\OrgSync\Services\SyncRemover\SyncRemoverInterface;
use LinkORB\OrgSync\SynchronizationAdapter\GroupPush\GroupPushInterface;
use LinkORB\OrgSync\SynchronizationAdapter\GroupPush\MattermostGroupPushAdapter;
use LinkORB\OrgSync\SynchronizationAdapter\OrganizationPull\OrganizationPullInterface;
use LinkORB\OrgSync\SynchronizationAdapter\SetPassword\MattermostSetPasswordAdapter;
use LinkORB\OrgSync\SynchronizationAdapter\SetPassword\SetPasswordInterface;
use LinkORB\OrgSync\SynchronizationAdapter\UserPush\MattermostUserPushAdapter;
use LinkORB\OrgSync\SynchronizationAdapter\UserPush\UserPushInterface;
use Pimple\Container;

class MattermostAdapterFactory implements AdapterFactoryInterface
{
    /** @var Driver */
    private $driver;

    /** @var string|null */
    private $defaultPassSalt;

    /** @var PasswordHelper */
    private $passwordHelper;

    public function __construct(?string $defaultPassSalt)
    {
        $this->defaultPassSalt = $defaultPassSalt;
    }

    public function createOrganizationPullAdapter(): OrganizationPullInterface
    {
        throw new BadMethodCallException('Not implemented yet');
    }

    public function createGroupPushAdapter(): GroupPushInterface
    {
        return new MattermostGroupPushAdapter($this->driver);
    }

    public function createUserPushAdapter(): UserPushInterface
    {
        return new MattermostUserPushAdapter(
            new ResponseChecker(User::class, [404]),
            $this->driver,
            $this->passwordHelper
        );
    }

    public function createSetPasswordAdapter(): SetPasswordInterface
    {
        return new MattermostSetPasswordAdapter(
            $this->driver,
            $this->passwordHelper,
            new ResponseChecker(User::class)
        );
    }

    public function setTarget(Target $target): AdapterFactoryInterface
    {
        assert($target instanceof Mattermost);

        if (!empty($target->getToken())) {
            $driverOpts = [
                'token' => $target->getToken()
            ];
        } else {
            $driverOpts = [
                'login_id' => $target->getLogin(),
                'password' => $target->getPassword(),
            ];
        }

        $driverOpts['url'] = $target->getBaseUrl();
        $driverOpts['scheme'] = $target->getScheme();

        $container = new Container([
            'driver' => $driverOpts
        ]);

        $this->driver = new Driver($container);
        $this->driver->authenticate();
        $this->passwordHelper = $this->getPasswordHelper($this->defaultPassSalt . $target->getName());

        return $this;
    }

    public function createSyncRemover(): SyncRemoverInterface
    {
        return new MattermostSyncRemover($this->driver, new BaseEntriesProvider($this->driver));
    }

    public function supports(string $action): bool
    {
        return in_array($action, [
            Target::GROUP_PUSH,
            Target::SET_PASSWORD,
            Target::USER_PUSH,
        ]);
    }

    protected function getPasswordHelper(?string $salt): PasswordHelper
    {
        return new PasswordHelper($salt);
    }
}
