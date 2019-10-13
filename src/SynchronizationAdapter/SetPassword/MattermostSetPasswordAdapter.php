<?php declare(strict_types=1);

namespace LinkORB\OrgSync\SynchronizationAdapter\SetPassword;

use Gnello\Mattermost\Driver;
use LinkORB\OrgSync\DTO\User;
use LinkORB\OrgSync\Services\Camunda\ResponseChecker;
use LinkORB\OrgSync\Services\PasswordHelper;

class MattermostSetPasswordAdapter implements SetPasswordInterface
{
    /** @var Driver */
    private $driver;

    /** @var PasswordHelper */
    private $passwordHelper;

    /** @var ResponseChecker */
    private $responseChecker;

    public function __construct(Driver $driver, PasswordHelper $passwordHelper, ResponseChecker $responseChecker)
    {
        $this->driver = $driver;
        $this->passwordHelper = $passwordHelper;
        $this->responseChecker = $responseChecker;
    }
    public function setPassword(User $user): SetPasswordInterface
    {
        $currentPassword = $user->getProperties()[User::PREVIOUS_PASSWORD]
            ?? $this->passwordHelper->getDefaultPassword($user->getUsername());

        $userInfoResponse = json_decode(
            (string) $this->driver->getUserModel()->getUserByUsername($user->getUsername())->getBody(),
            true
        );

        $response = $this->driver->getUserModel()->updateUserPassword(
            $userInfoResponse['id'] ?? '',
            [
                'current_password' => $currentPassword,
                'new_password' => $user->getPassword()
            ]
        );

        $this->responseChecker->assertResponse($response);

        return $this;
    }
}
