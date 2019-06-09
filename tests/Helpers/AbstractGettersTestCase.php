<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Tests\Helpers;

use PHPUnit\Framework\TestCase;

abstract class AbstractGettersTestCase extends TestCase
{
    abstract public function gettersDataProvider(): array;

    abstract public function getDtoClassName(): string;

    abstract public function getDefaultArgs(): array;

    /**
     * @param GettersTestCaseData $data
     * @dataProvider gettersDataProviderWrapper
     */
    public function testGetters(GettersTestCaseData $data)
    {
        $dto = $data->getDto();

        $this->assertSame($dto->{$data->getMethodName()}(), $data->getArgument());
    }

    public function gettersDataProviderWrapper(): array
    {
        $dataProvider = $this->gettersDataProvider();

        array_walk($dataProvider, function (array &$data) {
            $data = [$this->getBaseTestData(...$data)];
        });

        return $dataProvider;
    }

    private function getBaseTestData(string $key, $value): GettersTestCaseData
    {
        $args = $this->getDefaultArgs();

        $getter = 'get' . ucfirst($key);

        if (!method_exists($this->getDtoClassName(), $getter)) {
            $getter = $key;
        }

        $args[$key] = $value;

        return new GettersTestCaseData($this->getDtoClassName(), $args, $key, $getter);
    }
}
