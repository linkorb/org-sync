<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Services\Target;

use InvalidArgumentException;
use LinkORB\OrgSync\DTO\Target;

class TargetPool
{
    /** @var Target[] */
    private $pool;

    /** @var TargetFactory */
    private $factory;

    public function __construct(TargetFactory $factory, array $pool = [])
    {
        $this->factory = $factory;
        $this->pool = $pool;
    }

    public function addTarget(array $data): TargetPool
    {
        $target = $this->factory->create($data);

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
}