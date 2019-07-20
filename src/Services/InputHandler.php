<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Services;

use LinkORB\OrgSync\DTO\Organization;
use LinkORB\OrgSync\Services\Target\TargetPool;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class InputHandler
{
    /** @var TargetPool */
    private $targetsPool;

    /** @var DenormalizerInterface */
    private $denormalizer;

    public function __construct(TargetPool $targetsPool, DenormalizerInterface $denormalizer)
    {
        $this->targetsPool = $targetsPool;
        $this->denormalizer = $denormalizer;
    }

    /**
     * @param array $targets
     * @param array $organizations
     * @return Organization[]
     */
    public function handle(array $targets, array $organizations): array
    {
        $organizationDtos = [];

        foreach ($targets as $target) {
            $this->targetsPool->addTarget($target);
        }

        foreach ($organizations as $organization) {
            $organizationDtos[] = $this->denormalizer->denormalize($organization, Organization::class);
        }

        return $organizationDtos;
    }
}