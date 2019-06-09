<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Tests\Helpers;

class GettersTestCaseData
{
    /**
     * @var mixed
     */
    private $dto;

    /**
     * @var string
     */
    private $dtoClassname;

    /**
     * @var array
     */
    private $args;

    /**
     * @var int|string
     */
    private $argsIndex;

    /**
     * @var string
     */
    private $methodName;

    public function __construct(string $dtoClassname, array $args, $argsIndex, string $methodName)
    {
        $this->dtoClassname = $dtoClassname;
        $this->args = $args;
        $this->argsIndex = $argsIndex;
        $this->methodName = $methodName;
    }

    public function getDto()
    {
        if (!$this->dto) {
            $this->dto = new $this->dtoClassname(...array_values($this->args));
        }

        return $this->dto;
    }

    /**
     * @return mixed
     */
    public function getArgument()
    {
        return $this->args[$this->argsIndex];
    }

    /**
     * @return string
     */
    public function getMethodName(): string
    {
        return $this->methodName;
    }
}
