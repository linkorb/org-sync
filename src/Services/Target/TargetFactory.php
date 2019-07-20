<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Services\Target;

use LinkORB\OrgSync\DTO\Target;
use LinkORB\OrgSync\Exception\SyncTargetException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class TargetFactory implements TargetFactoryInterface
{
    private const TARGET_MAP = [
        'camunda' => Target\Camunda::class,
    ];

    /** @var DenormalizerInterface */
    private $denormalizer;

    public function __construct(DenormalizerInterface $denormalizer)
    {
        $this->denormalizer = $denormalizer;
    }

    public function create(array $data): Target
    {
        if (empty($data['type'])) {
            throw new SyncTargetException('You should specify correct target type');
        }

        return $this->denormalizer->denormalize($data, static::TARGET_MAP[$data['type']]);
    }
}