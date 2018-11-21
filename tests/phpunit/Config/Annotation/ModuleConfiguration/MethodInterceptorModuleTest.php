<?php
declare(strict_types=1);

namespace Test\SimplyCodedSoftware\IntegrationMessaging\Config\Annotation\ModuleConfiguration;

use Builder\Annotation\ServiceActivatorAnnotationTestCaseBuilder;
use Fixture\Annotation\Interceptor\ClassLevelInterceptorsAndMethodsExample;
use Fixture\Annotation\Interceptor\ClassLevelInterceptorsExample;
use Fixture\Annotation\Interceptor\EnrichInterceptorExample;
use Fixture\Annotation\Interceptor\GatewayInterceptorExample;
use Fixture\Annotation\Interceptor\ServiceActivatorMethodLevelInterceptorExample;
use Fixture\Annotation\MessageEndpoint\ServiceActivator\AllConfigurationDefined\ServiceActivatorWithAllConfigurationDefined;
use SimplyCodedSoftware\IntegrationMessaging\Annotation\MessageEndpoint;
use SimplyCodedSoftware\IntegrationMessaging\Annotation\ServiceActivator;
use SimplyCodedSoftware\IntegrationMessaging\Config\Annotation\InMemoryAnnotationRegistrationService;
use SimplyCodedSoftware\IntegrationMessaging\Config\Annotation\ModuleConfiguration\MethodInterceptorModule;
use SimplyCodedSoftware\IntegrationMessaging\Config\ConfigurableReferenceSearchService;
use SimplyCodedSoftware\IntegrationMessaging\Config\ConfigurationException;
use SimplyCodedSoftware\IntegrationMessaging\Config\NullObserver;
use SimplyCodedSoftware\IntegrationMessaging\Config\OrderedMethodInterceptor;
use SimplyCodedSoftware\IntegrationMessaging\Endpoint\ClassMethodInterceptor;
use SimplyCodedSoftware\IntegrationMessaging\Handler\Enricher\Converter\EnrichHeaderWithExpressionBuilder;
use SimplyCodedSoftware\IntegrationMessaging\Handler\Enricher\Converter\EnrichHeaderWithValueBuilder;
use SimplyCodedSoftware\IntegrationMessaging\Handler\Enricher\Converter\EnrichPayloadWithExpressionBuilder;
use SimplyCodedSoftware\IntegrationMessaging\Handler\Enricher\Converter\EnrichPayloadWithValueBuilder;
use SimplyCodedSoftware\IntegrationMessaging\Handler\Enricher\EnricherBuilder;
use SimplyCodedSoftware\IntegrationMessaging\Handler\Gateway\GatewayInterceptorBuilder;
use SimplyCodedSoftware\IntegrationMessaging\Handler\ServiceActivator\ServiceActivatorBuilder;
use Test\SimplyCodedSoftware\IntegrationMessaging\Handler\ServiceActivator\ServiceActivatorBuilderTest;

/**
 * Class MethodInterceptorModuleTest
 * @package Test\SimplyCodedSoftware\IntegrationMessaging\Config\Annotation\ModuleConfiguration
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 */
class MethodInterceptorModuleTest extends AnnotationConfigurationTest
{
    /**
     * @return mixed
     * @throws ConfigurationException
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     * @throws \SimplyCodedSoftware\IntegrationMessaging\MessagingException
     */
    public function test_registering_method_level_interceptor()
    {
        $expectedConfiguration = $this->createMessagingSystemConfiguration()
            ->registerPreCallMethodInterceptor(
                OrderedMethodInterceptor::create(
                    ServiceActivatorBuilder::create("authorizationService", "check")
                        ->withEndpointId("some-id"),
                    2
                )
            )
            ->registerPostCallMethodInterceptor(
                OrderedMethodInterceptor::create(
                    ServiceActivatorBuilder::create("test", "check")
                        ->withEndpointId("some-id"),
                    1
                )
            );

        $annotationRegistrationService = InMemoryAnnotationRegistrationService::createFrom([
            ServiceActivatorMethodLevelInterceptorExample::class
        ]);
        $annotationConfiguration = MethodInterceptorModule::create($annotationRegistrationService);
        $configuration = $this->createMessagingSystemConfiguration();
        $annotationConfiguration->prepare($configuration, [], ConfigurableReferenceSearchService::createEmpty());

        $this->assertEquals(
            $expectedConfiguration,
            $configuration
        );
    }

    /**
     * @throws ConfigurationException
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     * @throws \SimplyCodedSoftware\IntegrationMessaging\MessagingException
     */
    public function test_throwing_exception_if_lack_of_endpoints_for_interceptors()
    {
        $annotationRegistrationService = InMemoryAnnotationRegistrationService::createFrom([
            ServiceActivatorMethodLevelInterceptorExample::class
        ])
            ->resetClassMethodAnnotation(ServiceActivatorMethodLevelInterceptorExample::class, "send", ServiceActivator::class);

        $this->expectException(ConfigurationException::class);

        $annotationConfiguration = MethodInterceptorModule::create($annotationRegistrationService);
        $configuration = $this->createMessagingSystemConfiguration();
        $annotationConfiguration->prepare($configuration, [], ConfigurableReferenceSearchService::createEmpty());
    }

    /**
     * @throws ConfigurationException
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     * @throws \SimplyCodedSoftware\IntegrationMessaging\MessagingException
     */
    public function test_registering_class_level_interceptors()
    {
        $expectedConfiguration = $this->createMessagingSystemConfiguration()
            ->registerPreCallMethodInterceptor(
                OrderedMethodInterceptor::create(
                    ServiceActivatorBuilder::create("authorizationService", "check")
                        ->withEndpointId("some-id"),
                    OrderedMethodInterceptor::DEFAULT_ORDER_WEIGHT
                )
            )
            ->registerPostCallMethodInterceptor(
                OrderedMethodInterceptor::create(
                    ServiceActivatorBuilder::create("test", "check")
                        ->withEndpointId("some-id"),
                    OrderedMethodInterceptor::DEFAULT_ORDER_WEIGHT
                )
            );

        $annotationRegistrationService = InMemoryAnnotationRegistrationService::createFrom([
            ClassLevelInterceptorsExample::class
        ]);
        $annotationConfiguration = MethodInterceptorModule::create($annotationRegistrationService);
        $configuration = $this->createMessagingSystemConfiguration();
        $annotationConfiguration->prepare($configuration, [], ConfigurableReferenceSearchService::createEmpty());

        $this->assertEquals(
            $expectedConfiguration,
            $configuration
        );
    }

    /**
     * @throws ConfigurationException
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     * @throws \SimplyCodedSoftware\IntegrationMessaging\MessagingException
     */
    public function test_registering_class_level_and_methods_together_interceptors()
    {
        $expectedConfiguration = $this->createMessagingSystemConfiguration()
            ->registerPreCallMethodInterceptor(
                OrderedMethodInterceptor::create(
                    ServiceActivatorBuilder::create("authorizationService", "check")
                        ->withEndpointId("some-id"),
                    OrderedMethodInterceptor::DEFAULT_ORDER_WEIGHT
                )
            )
            ->registerPreCallMethodInterceptor(
                OrderedMethodInterceptor::create(
                    ServiceActivatorBuilder::create("validationCheck", "check")
                        ->withEndpointId("some-id"),
                    OrderedMethodInterceptor::DEFAULT_ORDER_WEIGHT
                )
            )
            ->registerPostCallMethodInterceptor(
                OrderedMethodInterceptor::create(
                    ServiceActivatorBuilder::create("test", "check")
                        ->withEndpointId("some-id"),
                    OrderedMethodInterceptor::DEFAULT_ORDER_WEIGHT
                )
            );

        $annotationRegistrationService = InMemoryAnnotationRegistrationService::createFrom([
            ClassLevelInterceptorsAndMethodsExample::class
        ]);
        $annotationConfiguration = MethodInterceptorModule::create($annotationRegistrationService);
        $configuration = $this->createMessagingSystemConfiguration();
        $annotationConfiguration->prepare($configuration, [], ConfigurableReferenceSearchService::createEmpty());

        $this->assertEquals(
            $expectedConfiguration,
            $configuration
        );
    }

    /**
     * @throws ConfigurationException
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     * @throws \SimplyCodedSoftware\IntegrationMessaging\MessagingException
     */
    public function test_registering_enrich_interceptor()
    {
        $expectedConfiguration = $this->createMessagingSystemConfiguration()
            ->registerPreCallMethodInterceptor(
                OrderedMethodInterceptor::create(
                    EnricherBuilder::create([
                        EnrichPayloadWithExpressionBuilder::createWithMapping("orders[*][person]", "payload", "requestContext['personId'] == replyContext['personId']")
                            ->withNullResultExpression("reference('fakeData').get()"),
                        EnrichPayloadWithExpressionBuilder::createWith("session1", "'some1'"),
                        EnrichPayloadWithValueBuilder::createWith("session2", "some2"),
                        EnrichHeaderWithExpressionBuilder::createWith("token1", "'123'")
                            ->withNullResultExpression("'1234'"),
                        EnrichHeaderWithValueBuilder::create("token2", "1234")
                    ])
                        ->withEndpointId("some-id")
                        ->withRequestHeaders([
                            "token" => "1234"
                        ])
                        ->withRequestPayloadExpression("payload['name']")
                        ->withRequestMessageChannel("requestChannel"),
                    OrderedMethodInterceptor::DEFAULT_ORDER_WEIGHT
                )
            );

        $annotationRegistrationService = InMemoryAnnotationRegistrationService::createFrom([
            EnrichInterceptorExample::class
        ]);
        $annotationConfiguration = MethodInterceptorModule::create($annotationRegistrationService);
        $configuration = $this->createMessagingSystemConfiguration();
        $annotationConfiguration->prepare($configuration, [], ConfigurableReferenceSearchService::createEmpty());

        $this->assertEquals(
            $expectedConfiguration,
            $configuration
        );
    }

    /**
     * @throws ConfigurationException
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     * @throws \SimplyCodedSoftware\IntegrationMessaging\MessagingException
     */
    public function test_registering_gateway_interceptor()
    {
        $expectedConfiguration = $this->createMessagingSystemConfiguration()
            ->registerPreCallMethodInterceptor(
                OrderedMethodInterceptor::create(
                    GatewayInterceptorBuilder::create("requestChannel")
                        ->withEndpointId("some-id"),
                    OrderedMethodInterceptor::DEFAULT_ORDER_WEIGHT
                )
            );

        $annotationRegistrationService = InMemoryAnnotationRegistrationService::createFrom([
            GatewayInterceptorExample::class
        ]);
        $annotationConfiguration = MethodInterceptorModule::create($annotationRegistrationService);
        $configuration = $this->createMessagingSystemConfiguration();
        $annotationConfiguration->prepare($configuration, [], ConfigurableReferenceSearchService::createEmpty());

        $this->assertEquals(
            $expectedConfiguration,
            $configuration
        );
    }
}