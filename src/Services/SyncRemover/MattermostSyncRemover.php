<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Services\SyncRemover;

use Gnello\Mattermost\Driver;
use LinkORB\OrgSync\DTO\Organization;

class MattermostSyncRemover implements SyncRemoverInterface
{
    /** @var Driver */
    private $driver;

    public function __construct(Driver $driver)
    {
        $this->driver = $driver;
    }

    public function removeNonExists(Organization $organization): void
    {
        $existingUsers = $this->getExistingUsers();

        // Removing users
        $syncUsers = [];
        foreach ($organization->getUsers() as $user) {
            $syncUsers[$user->getUsername()] = $user;
        }

        foreach ($existingUsers as $existingUser) {
            if (!isset($syncUsers[$existingUser['username']])) {
                $this->driver->getUserModel()->deactivateUserAccount($existingUser['id']);
            }
        }
    }

    private function getExistingUsers(): array
    {
        $users = json_decode(
            (string) $this->driver->getUserModel()->getUsers(['per_page' => 200])->getBody(),
            true
        );

        $users = array_filter($users, function (array $user) {
            return $user['delete_at'] === 0;
        });

        return array_map(function (array $user) {
            return [
                'username' => $user['username'] ?? '',
                'id' => $user['id'] ?? '',
            ];
        }, $users);
    }
}
