<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Tests\Unit\Services\Ldap;

use LinkORB\OrgSync\DTO\Group;
use LinkORB\OrgSync\Services\Ldap\LdapParentHelper;
use PHPUnit\Framework\TestCase;

class LdapParentHelperTest extends TestCase
{
    /** @var LdapParentHelper */
    private $parentHelper;

    protected function setUp(): void
    {
        $this->parentHelper = new LdapParentHelper();
    }

    /**
     * @dataProvider getParentData
     */
    public function testGetParentGroups(Group $group, array $expectedParents, array $initialParents = [])
    {
        $this->assertEquals($expectedParents, $this->parentHelper->getParentGroups($initialParents, $group));
    }

    public function testGetParentsCircularReference()
    {
        $this->expectExceptionMessage('Circular reference detected');
        $this->expectException(\UnexpectedValueException::class);

        $group1 = new Group('test1', '');
        $group2 = new Group('test2', '');
        $group1->setParent($group2);
        $group2->setParent($group1);

        $this->parentHelper->getParentGroups([], $group1);
    }

    public function getParentData(): array
    {
        return [
            [
                new Group('hello', '', '', new Group('to', '', '', new Group('all', '', '', new Group('world', '')))),
                ['to', 'all', 'world'],
            ],
            [
                new Group('empty', ''),
                [],
            ],
            [
                new Group('empty', ''),
                ['initial', 'set'],
                ['initial', 'set']
            ],
            [
                new Group('Earth', '', '', new Group('Eurasia', '', '', new Group('Europe', '',))),
                ['space', 'MilkyWay', 'Eurasia', 'Europe'],
                ['space', 'MilkyWay']
            ],
            [
                new Group('Earth', ''),
                [],
                []
            ],
        ];
    }
}
