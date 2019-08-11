<?php

namespace Test\Ecotone\Modelling\Fixture\Annotation\CommandHandler\Service;

use Ecotone\Messaging\Annotation\MessageEndpoint;
use Ecotone\Modelling\Annotation\CommandHandler;

/**
 * Class CommandHandlerWithReturnValue
 * @package Test\Ecotone\Modelling\Fixture\Annotation\CommandHandler\Service
 * @author  Dariusz Gafka <dgafka.mail@gmail.com>
 * @MessageEndpoint()
 */
class CommandHandlerWithAnnotationClassNameWithService
{
    /**
     * @param \stdClass $service
     * @return int
     * @CommandHandler(inputChannelName="input", endpointId="command-id", messageClassName=SomeCommand::class)
     */
    public function execute(\stdClass $service) : int
    {
        return 1;
    }
}