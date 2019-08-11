<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Services\Camunda;

use GuzzleHttp\Client;
use LinkORB\OrgSync\DTO\Group;
use LinkORB\OrgSync\DTO\User;

class CamundaGroupMemberProvider
{
    /**
     * @var Client
     */
    private $httpClient;

    /**
     * @var CamundaGroupMapper
     */
    private $mapper;

    public function __construct(Client $httpClient, CamundaGroupMapper $mapper)
    {
        $this->httpClient = $httpClient;
        $this->mapper = $mapper;
    }

    /**
     * @param User $user
     * @return Group[]
     */
    public function getGroupsForUser(User $user): array
    {
        $response = $this->httpClient->get(sprintf('group?member=%s', $user->getUsername()));

        $groups = [];

        foreach (json_decode($response->getBody()->getContents(), true) as $group) {
            $groupDto = $this->mapper->map($group);

            $groups[$groupDto->getName()] = $groupDto;
        }

        return $groups;
    }
}
