<?php


namespace Test\Ecotone\Messaging\Unit\Handler\Processor;


use Ecotone\Messaging\Handler\Processor\MethodInvoker\AroundInterceptorReference;
use Ecotone\Messaging\Support\InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Test\Ecotone\Messaging\Fixture\Annotation\Interceptor\ResolvedPointcut\AroundInterceptorExample;
use Test\Ecotone\Messaging\Fixture\Annotation\Interceptor\ResolvedPointcut\AttributeOne;
use Test\Ecotone\Messaging\Fixture\Annotation\Interceptor\ResolvedPointcut\AttributeThree;
use Test\Ecotone\Messaging\Fixture\Annotation\Interceptor\ResolvedPointcut\AttributeTwo;

class AroundInterceptorReferenceTest extends TestCase
{
    public function test_resolve_no_pointcut()
    {
        $interceptorClass = AroundInterceptorExample::class;
        $methodName = "withNoAttribute";
        $expectedPointcut = "";

        $this->assertEquals(
            AroundInterceptorReference::create($interceptorClass, AroundInterceptorExample::class, $methodName, 0, $expectedPointcut, []),
            AroundInterceptorReference::create($interceptorClass, AroundInterceptorExample::class, $methodName, 0, "", [])
        );
    }

    public function test_resolve_pointcut_for_single_attribute()
    {
        $interceptorClass = AroundInterceptorExample::class;
        $methodName = "withSingleAttribute";
        $expectedPointcut = "(" . AttributeOne::class . ")";

        $this->assertEquals(
            AroundInterceptorReference::create($interceptorClass, AroundInterceptorExample::class, $methodName, 0, $expectedPointcut, []),
            AroundInterceptorReference::create($interceptorClass, AroundInterceptorExample::class, $methodName, 0, "", [])
        );
    }

    public function test_resolve_pointcut_for_two_optional_attributes()
    {
        $interceptorClass = AroundInterceptorExample::class;
        $methodName = "withTwoOptionalAttributes";
        $expectedPointcut = "(" . AttributeOne::class  . ")||(" . AttributeTwo::class . ")";

        $this->assertEquals(
            AroundInterceptorReference::create($interceptorClass, AroundInterceptorExample::class, $methodName, 0, $expectedPointcut, []),
            AroundInterceptorReference::create($interceptorClass, AroundInterceptorExample::class, $methodName, 0, "", [])
        );
    }

    public function test_resolve_pointcut_for_union_attributes()
    {
        $interceptorClass = AroundInterceptorExample::class;
        $methodName = "withUnionAttributes";
        $expectedPointcut = "(" . AttributeOne::class  . "||" . AttributeTwo::class . ")";

        $this->assertEquals(
            AroundInterceptorReference::create($interceptorClass, AroundInterceptorExample::class, $methodName, 0, $expectedPointcut, []),
            AroundInterceptorReference::create($interceptorClass, AroundInterceptorExample::class, $methodName, 0, "", [])
        );
    }

    public function test_resolve_pointcut_for_two_required_attributes()
    {
        $interceptorClass = AroundInterceptorExample::class;
        $methodName = "withTwoRequiredAttributes";
        $expectedPointcut = "(" . AttributeOne::class  . ")&&(" . AttributeTwo::class . ")";

        $this->assertEquals(
            AroundInterceptorReference::create($interceptorClass, AroundInterceptorExample::class, $methodName, 0, $expectedPointcut, []),
            AroundInterceptorReference::create($interceptorClass, AroundInterceptorExample::class, $methodName, 0, "", [])
        );
    }

    public function test_resolve_pointcut_for_union_attributes_and_required()
    {
        $interceptorClass = AroundInterceptorExample::class;
        $methodName = "withUnionAttributesAndRequiredAttribute";
        $expectedPointcut = "(" . AttributeOne::class  . "||" . AttributeTwo::class . ")&&(" . AttributeThree::class . ")";

        $this->assertEquals(
            AroundInterceptorReference::create($interceptorClass, AroundInterceptorExample::class, $methodName, 0, $expectedPointcut, []),
            AroundInterceptorReference::create($interceptorClass, AroundInterceptorExample::class, $methodName, 0, "", [])
        );
    }

    public function test_group_optional_attributes_together()
    {
        $interceptorClass = AroundInterceptorExample::class;
        $methodName = "withOptionalAttributesAndRequired";
        $expectedPointcut = "(" . AttributeOne::class  . "||" . AttributeThree::class . ")&&(" . AttributeTwo::class . ")";

        $this->assertEquals(
            AroundInterceptorReference::create($interceptorClass, AroundInterceptorExample::class, $methodName, 0, $expectedPointcut, []),
            AroundInterceptorReference::create($interceptorClass, AroundInterceptorExample::class, $methodName, 0, "", [])
        );
    }

    public function test_throwing_exception_if_optional_union_type_given()
    {
        $interceptorClass = AroundInterceptorExample::class;
        $methodName = "withOptionalUnionAttributesAndRequiredAttribute";

        $this->expectException(InvalidArgumentException::class);

        AroundInterceptorReference::create($interceptorClass, AroundInterceptorExample::class, $methodName, 0, "", []);
    }

    public function test_throwing_exception_if_attribute_joined_with_non_attribute()
    {
        $interceptorClass = AroundInterceptorExample::class;
        $methodName = "withUnionTypeOfAttributeAndNonAttributeClass";

        $this->expectException(InvalidArgumentException::class);

        AroundInterceptorReference::create($interceptorClass, AroundInterceptorExample::class, $methodName, 0, "", []);
    }
}