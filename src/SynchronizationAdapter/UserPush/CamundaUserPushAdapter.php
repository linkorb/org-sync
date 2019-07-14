<?php declare(strict_types=1);

namespace LinkORB\OrgSync\SynchronizationAdapter\UserPush;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use LinkORB\OrgSync\DTO\User;
use LinkORB\OrgSync\Exception\SyncHttpException;
use LinkORB\OrgSync\Exception\UserSyncException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class CamundaUserPushAdapter implements UserPushInterface
{
    use CreateUpdateUserAwareTrait;

    /** @var Client */
    private $httpClient;

    public function __construct(Client $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function pushUser(User $user): UserPushInterface
    {
        $this->doPush($user);

        return $this;
    }

    protected function exists(User $user): bool
    {
        try {
            $response = $this->httpClient->get(sprintf('/user/%s/profile', $user->getUsername()));
        } catch (Throwable $exception) {
            throw new SyncHttpException($exception);
        }

        return $response->getStatusCode() === 200;
    }

    protected function create(User $user): void
    {
        try {
            $response = $this->httpClient->post(
                '/user/create',
                [
                    RequestOptions::JSON => [
                        'profile' => [
                            'id' => $user->getUsername(),
                            'firstName' => $user->getProperties()['firstName'] ?? null,
                            'lastName' => $user->getProperties()['lastName'] ?? null,
                            'email' => $user->getEmail(),
                        ],
                        'credentials' => [
                            'password' => $this->generatePassword(),
                        ],
                    ],
                ]
            );
        } catch (Throwable $exception) {
            throw new SyncHttpException($exception);
        }

        $this->assertCorrectResponse($response);
    }

    protected function update(User $user): void
    {
        try {
            $response = $this->httpClient->put(
                sprintf('/user/%s/profile', $user->getUsername()),
                [
                    RequestOptions::JSON => [
                        'id' => $user->getUsername(),
                        'firstName' => $user->getProperties()['firstName'] ?? null,
                        'lastName' => $user->getProperties()['lastName'] ?? null,
                        'email' => $user->getEmail(),
                    ],
                ]
            );
        } catch (Throwable $exception) {
            throw new SyncHttpException($exception);
        }

        $this->assertCorrectResponse($response);
    }

    private function assertCorrectResponse(ResponseInterface $response): void
    {
        if ($response->getStatusCode() >= 400) {
            throw new UserSyncException((string) $response->getBody(), $response->getStatusCode());
        }
    }

    protected function generatePassword(): string
    {
        return substr(md5(uniqid()), 0, 8);
    }
}
