<?php declare(strict_types=1);

namespace LinkORB\OrgSync\SynchronizationAdapter\SetPassword;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use LinkORB\OrgSync\DTO\User;
use LinkORB\OrgSync\Exception\SyncHttpException;
use LinkORB\OrgSync\Services\Camunda\ResponseChecker;
use LinkORB\OrgSync\Services\PasswordHelper;
use Throwable;

final class CamundaSetPasswordAdapter implements SetPasswordInterface
{
    /** @var Client */
    private $httpClient;

    /** @var PasswordHelper */
    private $passwordHelper;

    /** @var ResponseChecker */
    private $responseChecker;

    public function __construct(Client $httpClient, PasswordHelper $passwordHelper, ResponseChecker$responseChecker)
    {
        $this->httpClient = $httpClient;
        $this->passwordHelper = $passwordHelper;
        $this->responseChecker = $responseChecker;
    }

    public function setPassword(User $user, string $password): SetPasswordInterface
    {
        $authPassword = $user->getPassword() ?? $this->passwordHelper->getDefaultPassword($user->getUsername());

        try {
            $response = $this->httpClient->put(
                sprintf('user/%s/credentials', $user->getUsername()),
                [
                    RequestOptions::JSON => [
                        'password' => $password,
                        'authenticatedUserPassword' => $authPassword,
                    ],
                ]
            );
        } catch (Throwable $exception) {
            throw new SyncHttpException($exception);
        }

        $this->responseChecker->assertResponse($response);

        return $this;
    }
}
