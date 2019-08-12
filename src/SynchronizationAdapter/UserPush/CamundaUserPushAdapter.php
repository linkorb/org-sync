<?php declare(strict_types=1);

namespace LinkORB\OrgSync\SynchronizationAdapter\UserPush;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use LinkORB\OrgSync\DTO\User;
use LinkORB\OrgSync\Services\Camunda\ResponseChecker;
use LinkORB\OrgSync\Services\PasswordHelper;

final class CamundaUserPushAdapter implements UserPushInterface
{
    use CreateUpdateUserAwareTrait;

    /** @var Client */
    private $httpClient;

    /** @var PasswordHelper */
    private $passwordHelper;

    /** @var ResponseChecker */
    private $responseChecker;

    public function __construct(Client $httpClient, PasswordHelper $passwordHelper, ResponseChecker $responseChecker)
    {
        $this->httpClient = $httpClient;
        $this->passwordHelper = $passwordHelper;
        $this->responseChecker = $responseChecker;
    }

    public function pushUser(User $user): UserPushInterface
    {
        $this->doPushUser($user);

        return $this;
    }

    protected function exists(User $user): bool
    {
        $response = $this->httpClient->get(sprintf('user/%s/profile', $user->getUsername()));

        return $response->getStatusCode() === 200;
    }

    protected function create(User $user): void
    {
        $response = $this->httpClient->post(
            'user/create',
            [
                RequestOptions::JSON => [
                    'profile' => [
                        'id' => $user->getUsername(),
                        'firstName' => $user->getProperties()['firstName'] ?? null,
                        'lastName' => $user->getProperties()['lastName'] ?? null,
                        'email' => $user->getEmail(),
                    ],
                    'credentials' => [
                        'password' => $this->passwordHelper->getDefaultPassword($user->getUsername()),
                    ],
                ],
            ]
        );

        $this->responseChecker->assertResponse($response);
    }

    protected function update(User $user): void
    {
        $response = $this->httpClient->put(
            sprintf('user/%s/profile', $user->getUsername()),
            [
                RequestOptions::JSON => [
                    'id' => $user->getUsername(),
                    'firstName' => $user->getProperties()['firstName'] ?? null,
                    'lastName' => $user->getProperties()['lastName'] ?? null,
                    'email' => $user->getEmail(),
                ],
            ]
        );

        $this->responseChecker->assertResponse($response);
    }
}
