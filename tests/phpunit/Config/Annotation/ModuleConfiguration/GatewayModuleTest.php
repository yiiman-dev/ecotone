<?php
declare(strict_types=1);

namespace Test\SimplyCodedSoftware\IntegrationMessaging\Config\Annotation\ModuleConfiguration;

use Fixture\Annotation\MessageEndpoint\Gateway\GatewayWithReplyChannelExample;
use Fixture\Handler\Gateway\MultipleMethodsGatewayExample;
use SimplyCodedSoftware\IntegrationMessaging\Annotation\Gateway;
use SimplyCodedSoftware\IntegrationMessaging\Annotation\MessageEndpoint;
use SimplyCodedSoftware\IntegrationMessaging\Annotation\Parameter\Payload;
use SimplyCodedSoftware\IntegrationMessaging\Config\Annotation\InMemoryAnnotationRegistrationService;
use SimplyCodedSoftware\IntegrationMessaging\Config\Annotation\ModuleConfiguration\GatewayModule;
use SimplyCodedSoftware\IntegrationMessaging\Config\NullObserver;
use SimplyCodedSoftware\IntegrationMessaging\Handler\Gateway\CombinedGatewayBuilder;
use SimplyCodedSoftware\IntegrationMessaging\Handler\Gateway\CombinedGatewayDefinition;
use SimplyCodedSoftware\IntegrationMessaging\Handler\Gateway\GatewayProxyBuilder;

/**
 * Class AnnotationTransformerConfigurationTest
 * @package Test\SimplyCodedSoftware\IntegrationMessaging\Config\Annotation\Annotation
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 */
class GatewayModuleTest extends AnnotationConfigurationTest
{
    /**
     */
    public function test_creating_transformer_builder()
    {
        $gatewayAnnotation = new Gateway();
        $gatewayAnnotation->requestChannel = "requestChannel";
        $gatewayAnnotation->transactionFactories = ['dbalTransaction'];
        $gatewayAnnotation->errorChannel = "someErrorChannel";
        $messageToPayloadParameterAnnotation = new Payload();
        $messageToPayloadParameterAnnotation->parameterName = "orderId";
        $gatewayAnnotation->parameterConverters = [
            $messageToPayloadParameterAnnotation
        ];

        $annotationGatewayConfiguration = GatewayModule::create(
            $this->createAnnotationRegistrationService(
                GatewayWithReplyChannelExample::class,
                "buy",
                new MessageEndpoint(),
                $gatewayAnnotation
            )
        );

        $messagingSystemConfiguration = $this->createMessagingSystemConfiguration();
        $annotationGatewayConfiguration->prepare($messagingSystemConfiguration, []);

        $this->assertEquals(
            $this->createMessagingSystemConfiguration()
                ->registerGatewayBuilder(
                    GatewayProxyBuilder::create(
                        GatewayWithReplyChannelExample::class, GatewayWithReplyChannelExample::class,
                        "buy", "requestChannel"
                    )
                        ->withErrorChannel("someErrorChannel")
                        ->withTransactionFactories(['dbalTransaction'])
                ),
            $messagingSystemConfiguration
        );
    }

    /**
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public function test_registering_gateway_with_multiple_methods()
    {
        $annotationGatewayConfiguration = GatewayModule::create(InMemoryAnnotationRegistrationService::createFrom([MultipleMethodsGatewayExample::class]));

        $messagingSystemConfiguration = $this->createMessagingSystemConfiguration();
        $annotationGatewayConfiguration->prepare($messagingSystemConfiguration, []);

        $this->assertEquals(
            $this->createMessagingSystemConfiguration()
                ->registerGatewayBuilder(
                    CombinedGatewayBuilder::create(
                        MultipleMethodsGatewayExample::class,
                        MultipleMethodsGatewayExample::class,
                        [
                            CombinedGatewayDefinition::create(
                                GatewayProxyBuilder::create(
                                    MultipleMethodsGatewayExample::class, MultipleMethodsGatewayExample::class,
                                    "execute1", "channel1"
                                ),
                                "execute1"
                            ),
                            CombinedGatewayDefinition::create(
                                GatewayProxyBuilder::create(
                                    MultipleMethodsGatewayExample::class, MultipleMethodsGatewayExample::class,
                                    "execute2", "channel2"
                                ),
                                "execute2"
                            )
                        ]
                    )
                ),
            $messagingSystemConfiguration
        );
    }

    /**
     * @inheritDoc
     */
    protected function createAnnotationConfiguration(): string
    {
        return GatewayModule::class;
    }

    /**
     * @inheritDoc
     */
    protected function getPartOfTheNamespaceForTests(): string
    {
        return "MessageEndpoint\Gateway";
    }
}