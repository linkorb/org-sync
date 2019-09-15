# OrgSync
> Load an organizational structure (users + groups) from array. Push this data to multiple adapters

"Push" in this case means "Create or Update or Remove". When a User or Group is present in the backend service, but not in the local Organization object, the object should also be removed on the remote service.

List of adapters:
* [GitHub](https://github.com/) (WIP)
* [Camunda](https://docs.camunda.org/manual/7.9/)
* LDAP (Planned)
* [MatterMost](https://mattermost.com/) (Planned)

## Installation

Install the latest version with:

```sh
$ composer require linkorb/org-sync
```

## Usage

`LinkORB\OrgSync\SynchronizationMediator\SynchronizationMediator` performs basic syncing operations such as:
* sync organization
* sync user
* sync group
* set password
* pull organization (planned)

```php
$organization = $this->synchronizationMediator->initialize(
    $targetsArray,
    $organizationArray
);

$this->synchronizationMediator->pushOrganization($organization);
```

### Examples
##### Organization structure:

```php
Array
(
    [name] => Organization name
    [users] => Array
        (
            [username] => Array
                (
                    [email] => user@email
                    [displayName] => User Name
                    [avatar] => https://example.com/user_avatar.gif
                    [properties] => Array
                        (
                            [key] => value
                        )
                )
        )
    [groups] => Array
        (
            [group name] => Array
                    [parent] => parent group name
                    [displayName] => Group
                    [avatar] => https://example.com/team_avatar.png
                    [members] => Array
                        (
                            [0] => member1
                            [1] => member2
                        )
                    [properties] => Array
                        (
                            [key] => value
                        )
                    [targets] => Array
                        (
                            [0] => target name
                        )
                )
        )
)
```
Empty `members` and/or `targets` section under `groups` means all `members` and/or `targets` for that group.

##### Targets structure:

```php
Array
(
    [targetName] => Array
        (
            [type] => targetType
            [baseUrl] => http://172.17.0.1:8080/engine-rest/
        )
)
```

### Integrations
* [OrgSync CLI](https://github.com/linkorb/org-sync-cli)
