<?php declare(strict_types=1);

namespace LinkORB\OrgSync\SynchronizationAdapter\GroupPush;

use BadMethodCallException;
use Gnello\Mattermost\Driver;
use LinkORB\OrgSync\DTO\Group;
use LinkORB\OrgSync\DTO\User;
use Psr\Http\Message\ResponseInterface;

class MattermostGroupPushAdapter implements GroupPushInterface
{
    /** @var Driver */
    private $driver;

    public function __construct(Driver $driver)
    {
        $this->driver = $driver;
    }

    public function pushGroup(Group $group): GroupPushInterface
    {
        $existsResponse = $this->driver->getTeamModel()->getTeamByName($group->getName());

        if ($this->exists($existsResponse)) {
            $response = $this->update($this->getTeamId($existsResponse), $group);
        } else {
            $response = $this->create($group);
        }

        if ($response->getStatusCode() >= 400) {
            throw new BadMethodCallException('Unable to perform action. Try hard remove teams by CLI. See https://docs.mattermost.com/administration/command-line-tools.html');
        }

        if (empty($group->getMembers())) {
            return $this;
        }

        $teamId = $this->getTeamId($response);
        $membersIds = [];
        foreach ($group->getMembers() as $member) {
            $membersIds[] = $this->getMemberId($member);
        }

        $this->addMembers($teamId, $membersIds);

        return $this;
    }

    private function exists(ResponseInterface $response): bool
    {
        if ($response->getStatusCode() !== 200) {
            return false;
        }

        $teamData = json_decode((string) $response->getBody(), true);

        if ($teamData['delete_at'] !== 0) {
            return false;
        }

        return true;
    }

    private function create(Group $group): ResponseInterface
    {
        return $this->driver->getTeamModel()->createTeam([
            'name' => $group->getName(),
            'display_name' => $group->getDisplayName(),
            'type' => 'I',
        ]);
    }

    private function update(string $teamId, Group $group): ResponseInterface
    {
        return $this->driver->getTeamModel()->updateTeam($teamId, ['id' => $teamId, 'display_name' => $group->getDisplayName()]);
    }

    /**
     * @param string $teamId
     * @param string[] $userIds
     */
    private function addMembers(string $teamId, array $userIds): void
    {
        $entries = [];
        foreach ($userIds as $userId) {
            $entries[] = [
                'team_id' => $teamId,
                'user_id' => $userId,
            ];
        }

        $this->driver->getTeamModel()->addMultipleUsers($teamId, $entries);
    }

    private function getTeamId(ResponseInterface $response): string
    {
        $teamData = json_decode((string) $response->getBody(), true);

        return $teamData['id'];
    }

    private function getMemberId(User $user): string
    {
        $response = json_decode(
            (string) $this->driver->getUserModel()->getUserByUsername($user->getUsername())->getBody(),
            true
        );

        return $response['id'];
    }
}
