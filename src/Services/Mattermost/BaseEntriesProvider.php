<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Services\Mattermost;

use Gnello\Mattermost\Driver;
use Psr\Http\Message\ResponseInterface;

class BaseEntriesProvider
{
    /** @var Driver */
    private $driver;

    public function __construct(Driver $driver)
    {
        $this->driver = $driver;
    }

    public function getExistingUsers(): array
    {
        return array_map(function (array $user) {
            return [
                'username' => $user['username'] ?? '',
                'id' => $user['id'] ?? '',
            ];
        }, $this->getBaseEntries(function (int $limit) {
            return $this->driver->getUserModel()->getUsers(['per_page' => $limit]);
        }));
    }

    public function getExistingGroups(): array
    {
        return array_map(function (array $group) {
            return [
                'name' => $group['name'] ?? '',
                'id' => $group['id'] ?? '',
            ];
        }, $this->getBaseEntries(function (int $limit) {
            return $this->driver->getTeamModel()->getTeams(['per_page' => $limit]);
        }));
    }

    public function getTeamMembers(string $teamId): array
    {
        return array_map(function (array $member) {
            return $member['user_id'] ?? '';
        }, $this->getBaseEntries(function (int $limit) use ($teamId) {
            return $this->driver->getTeamModel()->getTeamMembers($teamId, ['per_page' => $limit]);
        }));
    }

    private function getBaseEntries(callable $driverFn): array
    {
        $entries = [];

        $limit = 200;
        do {
            /** @var ResponseInterface $response */
            $response = $driverFn($limit);

            $responseData = json_decode(
                (string)$response->getBody(),
                true
            );

            $entries = array_merge($entries, $responseData);
        } while (count($responseData) === $limit);

        return array_filter($entries, function (array $entry) {
            return $entry['delete_at'] === 0;
        });
    }
}
