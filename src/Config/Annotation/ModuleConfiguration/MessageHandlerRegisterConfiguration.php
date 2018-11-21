<?php
declare(strict_types=1);

namespace SimplyCodedSoftware\IntegrationMessaging\Config\Annotation\ModuleConfiguration;

use SimplyCodedSoftware\IntegrationMessaging\Annotation\MessageEndpoint;
use SimplyCodedSoftware\IntegrationMessaging\Config\Annotation\AnnotationModule;
use SimplyCodedSoftware\IntegrationMessaging\Config\Annotation\AnnotationRegistration;
use SimplyCodedSoftware\IntegrationMessaging\Config\Annotation\AnnotationRegistrationService;
use SimplyCodedSoftware\IntegrationMessaging\Config\ConfigurableReferenceSearchService;
use SimplyCodedSoftware\IntegrationMessaging\Config\Configuration;
use SimplyCodedSoftware\IntegrationMessaging\Handler\InterfaceToCall;
use SimplyCodedSoftware\IntegrationMessaging\Handler\MessageHandlerBuilder;
use SimplyCodedSoftware\IntegrationMessaging\Handler\MessageHandlerBuilderWithParameterConverters;

/**
 * Class BaseAnnotationConfiguration
 * @package SimplyCodedSoftware\IntegrationMessaging\Config\Annotation
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 * @internal
 */
abstract class MessageHandlerRegisterConfiguration extends NoExternalConfigurationModule implements AnnotationModule
{
    /**
     * @var array|MessageHandlerBuilder[]
     */
    private $messageHandlerBuilders;

    /**
     * AnnotationGatewayConfiguration constructor.
     *
     * @param MessageHandlerBuilder[] $messageHandlerBuilders
     */
    private function __construct(array $messageHandlerBuilders)
    {
        $this->messageHandlerBuilders = $messageHandlerBuilders;
    }

    /**
     * @inheritDoc
     */
    public static function create(AnnotationRegistrationService $annotationRegistrationService): AnnotationModule
    {
        $messageHandlerBuilders = [];
        $parameterConverterFactory = ParameterConverterAnnotationFactory::create();
        foreach ($annotationRegistrationService->findRegistrationsFor(MessageEndpoint::class, static::getMessageHandlerAnnotation()) as $annotationRegistration) {
            $annotation = $annotationRegistration->getAnnotationForMethod();
            $messageHandlerBuilders[] = static::createMessageHandlerFrom($annotationRegistration)
                ->withMethodParameterConverters(
                    $parameterConverterFactory->createParameterConverters(InterfaceToCall::create($annotationRegistration->getClassName(), $annotationRegistration->getMethodName()), $annotation->parameterConverters)
                );
        }

        return new static($messageHandlerBuilders);
    }

    /**
     * @return string
     */
    public static abstract function getMessageHandlerAnnotation(): string;

    /**
     * @param AnnotationRegistration $annotationRegistration
     * @return MessageHandlerBuilderWithParameterConverters
     */
    public static abstract function createMessageHandlerFrom(AnnotationRegistration $annotationRegistration): MessageHandlerBuilderWithParameterConverters;

    /**
     * @inheritDoc
     */
    public function prepare(Configuration $configuration, array $extensionObjects, ConfigurableReferenceSearchService $configurableReferenceSearchService): void
    {
        foreach ($this->messageHandlerBuilders as $messageHandlerBuilder) {
            $configuration->registerMessageHandler($messageHandlerBuilder);
        }
    }

    /**
     * @inheritDoc
     */
    public function canHandle($extensionObject): bool
    {
        return false;
    }
}