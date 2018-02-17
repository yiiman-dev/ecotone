<?php

namespace SimplyCodedSoftware\IntegrationMessaging\Handler\ServiceActivator;

use SimplyCodedSoftware\IntegrationMessaging\Handler\ChannelResolver;
use SimplyCodedSoftware\IntegrationMessaging\Handler\MessageHandlerBuilder;
use SimplyCodedSoftware\IntegrationMessaging\Handler\MessageHandlerBuilderWithParameterConverters;
use SimplyCodedSoftware\IntegrationMessaging\Handler\MessageToParameterConverter;
use SimplyCodedSoftware\IntegrationMessaging\Handler\MessageToParameterConverterBuilder;
use SimplyCodedSoftware\IntegrationMessaging\Handler\Processor\MethodInvoker\MethodInvoker;
use SimplyCodedSoftware\IntegrationMessaging\Handler\ReferenceSearchService;
use SimplyCodedSoftware\IntegrationMessaging\Handler\RequestReplyProducer;
use SimplyCodedSoftware\IntegrationMessaging\MessageHandler;
use SimplyCodedSoftware\IntegrationMessaging\Support\Assert;

/**
 * Class ServiceActivatorFactory
 * @package SimplyCodedSoftware\IntegrationMessaging\Handler\ServiceActivator
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 */
class ServiceActivatorBuilder implements MessageHandlerBuilderWithParameterConverters
{
    /**
     * @var string
     */
    private $objectToInvokeReferenceName;
    /**
     * @var string
     */
    private $methodName;
    /**
     * @var string
     */
    private $outputChannelName = "";
    /**
     * @var  bool
     */
    private $isReplyRequired = false;
    /**
     * @var array|\SimplyCodedSoftware\IntegrationMessaging\Handler\MessageToParameterConverterBuilder[]
     */
    private $methodParameterConverterBuilders = [];
    /**
     * @var string
     */
    private $inputMessageChannelName;
    /**
     * @var string
     */
    private $consumerName;
    /**
     * @var string[]
     */
    private $requiredReferenceNames;

    /**
     * ServiceActivatorBuilder constructor.
     * @param string $objectToInvokeOnReferenceName
     * @param string $methodName
     */
    private function __construct(string $objectToInvokeOnReferenceName, string $methodName)
    {
        $this->objectToInvokeReferenceName = $objectToInvokeOnReferenceName;
        $this->methodName = $methodName;
    }

    /**
     * @param string $objectToInvokeOnReferenceName
     * @param string $methodName
     * @return ServiceActivatorBuilder
     */
    public static function create(string $objectToInvokeOnReferenceName, string $methodName): self
    {
        return new self($objectToInvokeOnReferenceName, $methodName);
    }

    /**
     * @param bool $isReplyRequired
     * @return ServiceActivatorBuilder
     */
    public function withRequiredReply(bool $isReplyRequired): self
    {
        $this->isReplyRequired = $isReplyRequired;

        return $this;
    }

    /**
     * @param string $messageChannelName
     * @return ServiceActivatorBuilder
     */
    public function withOutputChannel(string $messageChannelName): self
    {
        $this->outputChannelName = $messageChannelName;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withMethodParameterConverters(array $methodParameterConverterBuilders): void
    {
        Assert::allInstanceOfType($methodParameterConverterBuilders, MessageToParameterConverterBuilder::class);

        $this->methodParameterConverterBuilders = $methodParameterConverterBuilders;
    }

    /**
     * @param string $inputMessageChannelName
     * @return ServiceActivatorBuilder
     */
    public function withInputMessageChannel(string $inputMessageChannelName) : self
    {
        $this->inputMessageChannelName = $inputMessageChannelName;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getInputMessageChannelName(): string
    {
        return $this->inputMessageChannelName;
    }

    /**
     * @inheritDoc
     */
    public function getRequiredReferenceNames(): array
    {
        $requiredReferenceNames = $this->requiredReferenceNames;
        $requiredReferenceNames[] = $this->objectToInvokeReferenceName;

        return $requiredReferenceNames;
    }

    /**
     * @inheritDoc
     */
    public function registerRequiredReference(string $referenceName): void
    {
        $this->requiredReferenceNames[] = $referenceName;
    }

    /**
     * @param string $name
     * @return ServiceActivatorBuilder
     */
    public function withConsumerName(string $name) : self
    {
        $this->consumerName = $name;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getConsumerName(): string
    {
        return $this->consumerName;
    }

    /**
     * @inheritdoc
     */
    public function build(ChannelResolver $channelResolver, ReferenceSearchService $referenceSearchService) : MessageHandler
    {
        $parameterConverters = [];
        foreach ($this->methodParameterConverterBuilders as $methodParameterConverterBuilder) {
            $parameterConverters[] = $methodParameterConverterBuilder->build($referenceSearchService);
        }

        $objectToInvoke = $this->objectToInvokeReferenceName;
        if (!$this->isStaticallyCalled()) {
            $objectToInvoke = $referenceSearchService->findByReference($this->objectToInvokeReferenceName);
        }

        return new ServiceActivatingHandler(
            RequestReplyProducer::createRequestAndReply(
                $this->outputChannelName,
                MethodInvoker::createWith(
                    $objectToInvoke,
                    $this->methodName,
                    $parameterConverters
                ),
                $channelResolver,
                $this->isReplyRequired
            )
        );
    }

    public function __toString()
    {
        return "service activator";
    }

    /**
     * @return bool
     */
    private function isStaticallyCalled(): bool
    {
        if (class_exists($this->objectToInvokeReferenceName)) {
            $referenceMethod = new \ReflectionMethod($this->objectToInvokeReferenceName, $this->methodName);

            if ($referenceMethod->isStatic()) {
                return true;
            }
        }

        return false;
    }
}