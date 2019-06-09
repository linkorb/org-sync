<?php declare(strict_types=1);

namespace LinkORB\OrgSync\DTO;

class Group
{
    /**
     * @var GroupSegment[]
     */
    private $segments;

    public function __construct(array $segments)
    {
        $this->segments = $segments;
    }

    /**
     * @return GroupSegment[]
     */
    public function getSegments(): array
    {
        return $this->segments;
    }
}
