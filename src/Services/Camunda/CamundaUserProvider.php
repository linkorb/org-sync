<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Services\Camunda;

use GuzzleHttp\Client;
use LinkORB\OrgSync\DTO\User;

class CamundaUserProvider
{
    /**
     * @var Client
     */
    private $httpClient;

    /**
     * @var CamundaUserMapper
     */
    private $mapper;

    public function __construct(Client $httpClient, CamundaUserMapper $mapper)
    {
        $this->httpClient = $httpClient;
        $this->mapper = $mapper;
    }

    /**
     * @return User[]
     */
    public function getUsers(): array
    {
        $response = $this->httpClient->get('user');

        $users = [];

        foreach (json_decode($response->getBody()->getContents(), true) as $user) {
            $userDto = $this->mapper->map($user);

            $users[$userDto->getUsername()] = $userDto;
        }

        return $users;
    }
}
