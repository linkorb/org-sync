<?php declare(strict_types=1);

namespace LinkORB\OrgSync\SynchronizationAdapter\GroupPush;

use Github\Client;
use Http\Client\Exception;
use LinkORB\OrgSync\DTO\Group;
use LinkORB\OrgSync\Services\InputHandler;

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
                $params['parent_team_id'] = $group->getParent()->getName();
            }

            $this->client->team()->update($group->getProperties()[InputHandler::GITHUB_ORGANIZATION], $params);
        } catch (Exception $e) {
            $this->client->team()->create($group->getProperties()[InputHandler::GITHUB_ORGANIZATION], $params ?? []);
        }

        foreach ($group->getMembers() as $member) {
            $this->client->team()->addMember($group->getName(), $member->getUsername());
        }

        return $this;
    }
}
