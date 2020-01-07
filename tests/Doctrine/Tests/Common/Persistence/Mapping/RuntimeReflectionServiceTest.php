<?php

namespace Doctrine\Tests\Common\Persistence\Mapping;

use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\Common\Persistence\Mapping\RuntimeReflectionService;
use Doctrine\Common\Reflection\RuntimePublicReflectionProperty;
use Doctrine\Common\Reflection\TypedNoDefaultReflectionProperty;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use function count;

/**
 * @group DCOM-93
 */
class RuntimeReflectionServiceTest extends TestCase
{

    /** @var RuntimeReflectionService */
    private $reflectionService;

    /** @var mixed */
    public $unusedPublicProperty;

    public function setUp()
    {
        $this->reflectionService = new RuntimeReflectionService();
    }

    public function testShortname()
    {
        self::assertSame('RuntimeReflectionServiceTest', $this->reflectionService->getClassShortName(self::class));
    }

    public function testClassNamespaceName()
    {
        self::assertSame('Doctrine\Tests\Common\Persistence\Mapping', $this->reflectionService->getClassNamespace(self::class));
    }

    public function testGetParentClasses()
    {
        $classes = $this->reflectionService->getParentClasses(self::class);
        self::assertTrue(count($classes) >= 1, 'The test class ' . self::class . ' should have at least one parent.');
    }

    public function testGetParentClassesForAbsentClass()
    {
        $this->expectException(MappingException::class);
        $this->reflectionService->getParentClasses(__NAMESPACE__ . '\AbsentClass');
    }

    public function testGetReflectionClass()
    {
        $class = $this->reflectionService->getClass(self::class);
        self::assertInstanceOf('ReflectionClass', $class);
    }

    public function testGetMethods()
    {
        self::assertTrue($this->reflectionService->hasPublicMethod(self::class, 'testGetMethods'));
        self::assertFalse($this->reflectionService->hasPublicMethod(self::class, 'testGetMethods2'));
    }

    public function testGetAccessibleProperty()
    {
        $reflProp = $this->reflectionService->getAccessibleProperty(self::class, 'reflectionService');
        self::assertInstanceOf(ReflectionProperty::class, $reflProp);
        self::assertInstanceOf(RuntimeReflectionService::class, $reflProp->getValue($this));

        $reflProp = $this->reflectionService->getAccessibleProperty(self::class, 'unusedPublicProperty');
        self::assertInstanceOf(RuntimePublicReflectionProperty::class, $reflProp);
        if (PHP_VERSION_ID >= 70400) {
            $reflProp = $this->reflectionService->getAccessibleProperty(PHP74TypedProperty::class, 'default');
            self::assertInstanceOf(ReflectionProperty::class, $reflProp);

            $reflProp = $this->reflectionService->getAccessibleProperty(PHP74TypedProperty::class, 'noDefault');
            self::assertInstanceOf(TypedNoDefaultReflectionProperty::class, $reflProp);
        }
    }
}
if (PHP_VERSION_ID >= 70400) {
    class PHP74TypedProperty
    {

        private string $noDefault;

        private string $default = 'foo';

    }
}
