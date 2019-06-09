<?php declare(strict_types=1);

namespace LinkORB\OrgSync\AdapterFactory;

use InvalidArgumentException;

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

    public function get(string $key): AdapterFactoryInterface
    {
        if (!isset($this->map[$key])) {
            throw new InvalidArgumentException('Trying to get non-existing factory');
        }

        return $this->map[$key];
    }
}
