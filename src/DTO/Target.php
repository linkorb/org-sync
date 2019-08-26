<?php declare(strict_types=1);

namespace LinkORB\OrgSync\DTO;

use Symfony\Component\Serializer\Annotation\DiscriminatorMap;

/**
 * @DiscriminatorMap(typeProperty="type", mapping={
 *    "camunda"="LinkORB\OrgSync\DTO\Target\Camunda",
 *    "github"="LinkORB\OrgSync\DTO\Target\Github",
 * })
 */
abstract class Target
{
    public const USER_PUSH = 'push_user';
    public const GROUP_PUSH = 'push_group';
    public const SET_PASSWORD = 'set_password';
    public const PULL_ORGANIZATION = 'organization_pull';

    /** @var string */
    private $baseUrl;

    /** @var string */
    private $name;

    public function __construct(string $baseUrl, string $name)
    {
        $this->baseUrl = $baseUrl;
        $this->name = $name;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
