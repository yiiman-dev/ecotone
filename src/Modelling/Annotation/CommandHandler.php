<?php

namespace Ecotone\Modelling\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;
use Ecotone\Messaging\Annotation\InputOutputEndpointAnnotation;
use Ramsey\Uuid\Uuid;

#[\Attribute(\Attribute::TARGET_METHOD)]
class CommandHandler extends InputOutputEndpointAnnotation
{
    /**
     * If @Aggregate was not found, message can be dropped instead of throwing exception
     */
    public bool $dropMessageOnNotFound = false;
    public array $identifierMetadataMapping = [];

    public function __construct(string $routingKey = "", string $endpointId = "", string $outputChannelName = "", bool $dropMessageOnNotFound = false, $identifierMetadataMapping = [], array $requiredInterceptorNames = [])
    {
        parent::__construct($routingKey, $endpointId, $outputChannelName, $requiredInterceptorNames);

        $this->dropMessageOnNotFound = $dropMessageOnNotFound;
        $this->identifierMetadataMapping = $identifierMetadataMapping;
    }

    public function isDropMessageOnNotFound(): bool
    {
        return $this->dropMessageOnNotFound;
    }

    public function getIdentifierMetadataMapping(): array
    {
        return $this->identifierMetadataMapping;
    }
}