<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Services\Camunda;

use GuzzleHttp\Client;
use LinkORB\OrgSync\DTO\Group;

class CamundaGroupProvider
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
     * @return Group[]
     */
    public function getGroups(): array
    {
        $response = $this->httpClient->get('group');

        $groups = [];

        foreach (json_decode($response->getBody()->getContents()) as $group) {
            $groupDto = $this->mapper->map($group);

            $groups[$groupDto->getName()] = $groupDto;
        }

        return $groups;
    }
}
