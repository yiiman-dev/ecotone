<?php
declare(strict_types=1);

namespace Test\Ecotone\Messaging\Unit\Config\Annotation\ModuleConfiguration;

use Doctrine\Common\Annotations\AnnotationException;
use Ecotone\Messaging\Config\Annotation\InMemoryAnnotationRegistrationService;
use Ecotone\Messaging\Config\Annotation\ModuleConfiguration\ConverterModule;
use Ecotone\Messaging\Config\Annotation\ModuleConfiguration\SerializerModule;
use Ecotone\Messaging\Config\ModuleReferenceSearchService;
use Ecotone\Messaging\Conversion\MediaType;
use Ecotone\Messaging\Endpoint\EventDriven\EventDrivenConsumerBuilder;
use Ecotone\Messaging\Gateway\Converter\Serializer;
use Ecotone\Messaging\Handler\InMemoryReferenceSearchService;
use Ecotone\Messaging\Handler\TypeDefinitionException;
use Ecotone\Messaging\MessagingException;
use ReflectionException;
use stdClass;
use Test\Ecotone\Messaging\Fixture\Annotation\Converter\ExampleConverterService;
use Test\Ecotone\Messaging\Fixture\Annotation\Converter\ExampleSingleConverterService;

/**
 * Class ConverterModuleTest
 * @package Test\Ecotone\Messaging\Unit\Config\Annotation\ModuleConfiguration
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 */
class SerializerModuleTest extends AnnotationConfigurationTest
{
    /**
     * @throws AnnotationException
     * @throws ReflectionException
     * @throws TypeDefinitionException
     * @throws MessagingException
     */
    public function test_registering_converters()
    {
        $annotationRegistrationService = InMemoryAnnotationRegistrationService::createEmpty()
            ->registerClassWithAnnotations(ExampleSingleConverterService::class);
        $configuration = $this->createMessagingSystemConfiguration();

        $converterModule = ConverterModule::create($annotationRegistrationService);
        $converterModule->prepare($configuration, [], ModuleReferenceSearchService::createEmpty());

        $serializerModule = SerializerModule::create($annotationRegistrationService);
        $serializerModule->prepare($configuration, [], ModuleReferenceSearchService::createEmpty());

        $configuration->registerConsumerFactory(new EventDrivenConsumerBuilder());
        $messagingSystem = $configuration->buildMessagingSystemFromConfiguration(InMemoryReferenceSearchService::createWith([
            ExampleSingleConverterService::class => new ExampleSingleConverterService()
        ]));
        /** @var Serializer $gateway */
        $gateway = $messagingSystem->getGatewayByName(Serializer::class);

        $this->assertEquals(
            new \stdClass(),
            $gateway->convertFromPHP("test", MediaType::createApplicationXPHPWithTypeParameter(\stdClass::class)->toString())
        );
    }
}