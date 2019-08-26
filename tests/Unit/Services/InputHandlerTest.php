<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Tests\Unit\Services;

use Doctrine\Common\Annotations\AnnotationReader;
use LinkORB\OrgSync\DTO\Group;
use LinkORB\OrgSync\DTO\Organization;
use LinkORB\OrgSync\DTO\Target;
use LinkORB\OrgSync\DTO\User;
use LinkORB\OrgSync\Services\Denormalizer\AssociativeArrayDenormalizer;
use LinkORB\OrgSync\Services\InputHandler;
use LinkORB\OrgSync\Services\Target\TargetPool;
use LinkORB\OrgSync\Tests\Helpers\OrganizationDataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class InputHandlerTest extends TestCase
{
    /** @var InputHandler */
    private $inputHandler;

    protected function setUp(): void
    {
        $metadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $normalizer = new ObjectNormalizer(
            $metadataFactory,
            null,
            null,
            new PropertyInfoExtractor([], [new PhpDocExtractor(), new ReflectionExtractor()])
        );
        $serializer = new Serializer([$normalizer, new AssociativeArrayDenormalizer()]);

        $this->inputHandler = new InputHandler(new TargetPool($serializer), $serializer);

        parent::setUp();
    }

    /**
     * @dataProvider getHandleData
     */
    public function testDenormalization(array $targets, array $organization)
    {
        /** @var Target[] $targets */
        $targetDtos = OrganizationDataProvider::transformToTargets($targets);
        /** @var User[] $users */
        $userDtos = OrganizationDataProvider::transformToUsers($organization['users']);
        $groups = OrganizationDataProvider::transformToGroups(
            $organization['groups'],
            $userDtos,
            $targetDtos,
            $organization['name']
        );

        $this->assertEquals(
            new Organization($organization['name'], $userDtos, $groups),
            $this->inputHandler->handle($targets, $organization)
        );
        $this->assertEquals($targetDtos, $this->inputHandler->getTargets());
    }

    // TODO: add more data
    public function getHandleData(): array
    {
        return [
            OrganizationDataProvider::provideArray(),
        ];
    }
}
