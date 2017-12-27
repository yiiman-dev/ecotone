<?php

namespace Messaging\Handler\ServiceActivator;

use Messaging\Handler\ChannelResolver;
use Messaging\Handler\MessageHandlerBuilder;
use Messaging\Handler\MethodArgument;
use Messaging\Handler\Processor\MethodInvoker\MethodInvoker;
use Messaging\Handler\RequestReplyProducer;
use Messaging\MessageChannel;
use Messaging\MessageHandler;
use Messaging\Support\Assert;

/**
 * Class ServiceActivatorFactory
 * @package Messaging\Handler\ServiceActivator
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 */
class ServiceActivatorBuilder implements MessageHandlerBuilder
{
    /**
     * @var object
     */
    private $objectToInvokeOn;
    /**
     * @var string
     */
    private $methodName;
    /**
     * @var MessageChannel
     */
    private $outputChannel;
    /**
     * @var  bool
     */
    private $isReplyRequired = false;
    /**
     * @var array|MethodArgument[]
     */
    private $methodArguments = [];
    /**
     * @var MessageChannel
     */
    private $inputMessageChannel;
    /**
     * @var string
     */
    private $messageHandlerName;
    /**
     * @var ChannelResolver
     */
    private $channelResolver;

    /**
     * ServiceActivatorBuilder constructor.
     * @param $objectToInvokeOn
     * @param string $methodName
     */
    private function __construct($objectToInvokeOn, string $methodName)
    {
        $this->objectToInvokeOn = $objectToInvokeOn;
        $this->methodName = $methodName;
    }

    /**
     * @param $objectToInvokeOn
     * @param string $methodName
     * @return ServiceActivatorBuilder
     */
    public static function create($objectToInvokeOn, string $methodName): self
    {
        return new self($objectToInvokeOn, $methodName);
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
     * @param MessageChannel $messageChannel
     * @return ServiceActivatorBuilder
     */
    public function withOutputChannel(MessageChannel $messageChannel): self
    {
        $this->outputChannel = $messageChannel;

        return $this;
    }

    /**
     * @param array|MethodArgument[] $methodArguments
     * @return ServiceActivatorBuilder
     */
    public function withMethodArguments(array $methodArguments): self
    {
        $this->methodArguments = $methodArguments;

        return $this;
    }

    /**
     * @param MessageChannel $inputMessageChannel
     * @return ServiceActivatorBuilder
     */
    public function withInputMessageChannel(MessageChannel $inputMessageChannel) : self
    {
        $this->inputMessageChannel = $inputMessageChannel;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setChannelResolver(ChannelResolver $channelResolver): MessageHandlerBuilder
    {
        $this->channelResolver = $channelResolver;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getInputMessageChannel(): MessageChannel
    {
        return $this->inputMessageChannel;
    }

    /**
     * @param string $name
     * @return ServiceActivatorBuilder
     */
    public function withName(string $name) : self
    {
        $this->messageHandlerName = $name;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function messageHandlerName(): string
    {
        return $this->messageHandlerName;
    }

    /**
     * @return MessageHandler
     */
    public function build(): MessageHandler
    {
        Assert::notNullAndEmpty($this->channelResolver, "You must pass channel resolver to Service Activator Builder");

        return new ServiceActivatingHandler(
            RequestReplyProducer::createFrom(
                $this->outputChannel,
                MethodInvoker::createWith(
                    $this->objectToInvokeOn,
                    $this->methodName,
                    $this->methodArguments
                ),
                $this->channelResolver,
                $this->isReplyRequired
            )
        );
    }
}