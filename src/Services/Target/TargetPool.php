<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Services\Target;

use InvalidArgumentException;
use LinkORB\OrgSync\DTO\Target;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class TargetPool
{
    /** @var Target[] */
    private $pool;

    /** @var DenormalizerInterface */
    private $denormalizer;

    public function __construct(DenormalizerInterface $denormalizer, array $pool = [])
    {
        $this->denormalizer = $denormalizer;
        $this->pool = $pool;
    }

    public function addTarget(array $data): TargetPool
    {
        $target = $this->denormalizer->denormalize($data, Target::class);

        $this->pool[$target->getName()] = $target;

        return $this;
    }

    public function get(string $name): Target
    {
        if (empty($this->pool[$name])) {
            throw new InvalidArgumentException('Trying to get non-configured target');
        }

        return $this->pool[$name];
    }

    public function all(): array
    {
        return $this->pool;
    }
}