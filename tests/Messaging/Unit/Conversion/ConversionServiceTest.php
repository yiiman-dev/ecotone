<?php
declare(strict_types=1);

namespace Test\Ecotone\Messaging\Unit\Conversion;

use PHPUnit\Framework\TestCase;
use Ecotone\Messaging\Conversion\AutoCollectionConversionService;
use Ecotone\Messaging\Conversion\MediaType;
use Ecotone\Messaging\Conversion\SerializedToObject\DeserializingConverter;
use Ecotone\Messaging\Handler\TypeDescriptor;

/**
 * Class ConversionServiceTest
 * @package Test\Ecotone\Messaging\Unit\Conversion
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 */
class ConversionServiceTest extends TestCase
{
    /**
     * @throws \Ecotone\Messaging\Handler\TypeDefinitionException
     * @throws \Ecotone\Messaging\MessagingException
     * @throws \Ecotone\Messaging\Support\InvalidArgumentException
     */
    public function test_using_php_serializing_converters()
    {
        $conversionService = AutoCollectionConversionService::createWith([
            new DeserializingConverter(),
            new \Ecotone\Messaging\Conversion\ObjectToSerialized\SerializingConverter()
        ]);


        $serializedObject = new \stdClass();
        $serializedObject->name = "johny";
        $serializedObject->age = 15;

        $result = $conversionService->convert(
            $serializedObject,
            TypeDescriptor::create(TypeDescriptor::OBJECT),
            MediaType::createApplicationXPHP(),
            TypeDescriptor::create(TypeDescriptor::STRING),
            MediaType::createApplicationXPHPSerialized()
        );

        $this->assertEquals(
            $serializedObject,
            $conversionService->convert(
                $result,
                TypeDescriptor::create(TypeDescriptor::STRING),
                MediaType::createApplicationXPHPSerialized(),
                TypeDescriptor::create(\stdClass::class),
                MediaType::createApplicationXPHP()
            )
        );
    }

    public function test_not_converting_when_source_is_null()
    {
        $conversionService = AutoCollectionConversionService::createWith([new \Ecotone\Messaging\Conversion\ObjectToSerialized\SerializingConverter()]);

        $this->assertEquals(
            null,
            $conversionService->convert(
                null,
                TypeDescriptor::create(TypeDescriptor::OBJECT),
                MediaType::createApplicationXPHP(),
                TypeDescriptor::create(TypeDescriptor::STRING),
                MediaType::createApplicationXPHPSerialized()
            )
        );
    }
}