<?php declare(strict_types=1);

namespace LinkORB\OrgSync\DTO;

use Symfony\Component\Serializer\Annotation\DiscriminatorMap;

/**
 * @DiscriminatorMap(typeProperty="type", mapping={
 *    "camunda"="LinkORB\OrgSync\DTO\Target\Camunda",
 * })
 */
abstract class Target
{
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
