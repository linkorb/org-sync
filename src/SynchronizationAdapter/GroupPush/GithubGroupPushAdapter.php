<?php declare(strict_types=1);

namespace LinkORB\OrgSync\SynchronizationAdapter\GroupPush;

use Github\Client;
use Http\Client\Exception;
use InvalidArgumentException;
use LinkORB\OrgSync\DTO\Group;
use LinkORB\OrgSync\Services\InputHandler;
use stdClass;
use Throwable;

class GithubGroupPushAdapter  implements GroupPushInterface
{
    /**
     * @var Client
     */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function pushGroup(Group $group): GroupPushInterface
    {
        assert(!empty($group->getProperties()[InputHandler::GITHUB_ORGANIZATION]));

        try {
            $params = ['name' => $group->getName()];

            if ($group->getParent()) {
               $this->setParentId(
                    $group->getProperties()[InputHandler::GITHUB_ORGANIZATION],
                    $group->getParent()->getName(),
                    $params
               );
            }

            $responseData = $this->getGroupByName(
                $group->getProperties()[InputHandler::GITHUB_ORGANIZATION],
                $group->getName()
            );

            $groupData = $this->client->team()->update($responseData->id, $params);
        } catch (Exception $e) {
            $groupData = $this->client->team()->create($group->getProperties()[InputHandler::GITHUB_ORGANIZATION], $params ?? []);
        }

        foreach ($group->getMembers() as $member) {
            $this->client->team()->addMember($groupData['id'], $member->getUsername());
        }

        return $this;
    }

    private function getGroupByName(string $organization, string $name): stdClass
    {
        $response = $this->client
            ->getHttpClient()
            ->get(
                sprintf(
                    'orgs/%s/teams/%s',
                    $organization,
                    $name
                )
            );

        return json_decode($response->getBody()->getContents());
    }

    private function setParentId(string $organization, string $groupName, array &$params): void
    {
        try {
            $parentResponseData = $this->getGroupByName($organization, $groupName);

            $params['parent_team_id'] = $parentResponseData->id;
        } catch (Throwable $e) {
            throw new InvalidArgumentException('Parent group not exists yet', 0, $e);
        }
    }
}
