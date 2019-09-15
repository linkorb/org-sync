<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Services\SyncRemover;

use LinkORB\OrgSync\DTO\Organization;
use LinkORB\OrgSync\Services\Ldap\Client;

class LdapSyncRemover implements SyncRemoverInterface
{
    /** @var Client */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function removeNonExists(Organization $organization): void
    {
        $existingUsers = $this->client->all(
            $this->client->search('(objectClass=inetOrgPerson)')
        );

        // Remove `count` key from array
        array_shift($existingUsers);

        // Removing users
        $syncUsers = [];
        foreach ($organization->getUsers() as $user) {
            $syncUsers[$user->getUsername()] = $user;
        }

        foreach ($existingUsers as $existingUser) {
            if (!isset($syncUsers[$existingUser['cn'][0]])) {
                $this->client->remove($existingUser['dn']);
            }
        }
    }
}
