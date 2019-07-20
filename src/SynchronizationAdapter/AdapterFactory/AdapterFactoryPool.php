<?php declare(strict_types=1);

namespace LinkORB\OrgSync\SynchronizationAdapter\AdapterFactory;

use InvalidArgumentException;
use LinkORB\OrgSync\DTO\Target;

class AdapterFactoryPool implements AdapterFactoryPoolInterface
{
    /**
     * @var AdapterFactoryInterface[]
     */
    private $map;

    public function __construct(array $map)
    {
        $this->map = $map;
    }

    public function get(Target $target): AdapterFactoryInterface
    {
        if (!isset($this->map[get_class($target)])) {
            throw new InvalidArgumentException('Trying to get non-existing factory');
        }

        return $this->map[get_class($target)]->setTarget($target);
    }
}
