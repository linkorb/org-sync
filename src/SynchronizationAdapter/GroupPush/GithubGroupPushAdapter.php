<?php declare(strict_types=1);

namespace LinkORB\OrgSync\SynchronizationAdapter\GroupPush;

use Github\Client;
use Http\Client\Exception;
use LinkORB\OrgSync\DTO\Group;

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
        try {
            $params = [
                'name' => $group->getName(),
                'parent_team_id' => $group->getParent() ? $group->getParent()->getName() : null,
            ];

            $this->client->team()->update($group->getName(), $params);
        } catch (Exception $e) {
            $this->client->team()->create($group->getName(), $params ?? []);
        }

        foreach ($group->getMembers() as $member) {
            $this->client->team()->addMember($group->getName(), $member->getUsername());
        }

        return $this;
    }
}
