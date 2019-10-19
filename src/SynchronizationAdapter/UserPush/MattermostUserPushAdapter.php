<?php declare(strict_types=1);

namespace LinkORB\OrgSync\SynchronizationAdapter\UserPush;

use Gnello\Mattermost\Driver;
use LinkORB\OrgSync\DTO\User;
use LinkORB\OrgSync\Services\ResponseChecker;
use LinkORB\OrgSync\Services\PasswordHelper;

class MattermostUserPushAdapter implements UserPushInterface
{
    /** @var Driver */
    private $driver;

    /** @var ResponseChecker */
    private $checker;

    /** @var PasswordHelper */
    private $passwordHelper;

    public function __construct(ResponseChecker $checker, Driver $driver, PasswordHelper $passwordHelper)
    {
        $this->checker = $checker;
        $this->driver = $driver;
        $this->passwordHelper = $passwordHelper;
    }

    public function pushUser(User $user): UserPushInterface
    {
        $response = $this->driver->getUserModel()->getUserByUsername($user->getUsername());
        $this->checker->assertResponse($response);

        $requestOptions = $this->getRequestOptions($user);

        if ($response->getStatusCode() === 200) {
            $responseBody = json_decode((string) $response->getBody(), true);

            if ($responseBody['delete_at'] !== 0) {
                $this->driver->getUserModel()->updateUserActive($responseBody['id'] ?? '', ['active' => true]);
            }

            $response = $this->driver->getUserModel()->patchUser($responseBody['id'] ?? '', $requestOptions);
        } else {
            $requestOptions['password'] = $this->passwordHelper->getDefaultPassword($user->getUsername());

            $response = $this->driver->getUserModel()->createUser($requestOptions);
        }

        $this->checker->assertResponse($response);

        return $this;
    }

    private function getRequestOptions(User $user): array
    {
        return [
            'email' => $user->getEmail(),
            'username' => $user->getUsername(),
            'first_name' => $user->getProperties()['firstName'] ?? null,
            'last_name' => $user->getProperties()['lastName'] ?? null,
            'nickname' => $user->getDisplayName(),
        ];
    }
}
