<?php

namespace Ecotone\Messaging\Gateway;

use Ecotone\Messaging\Attribute\MessageGateway;
use Ecotone\Messaging\Attribute\Parameter\Header;
use Ecotone\Messaging\Attribute\Parameter\Payload;
use Ecotone\Messaging\Config\Annotation\ModuleConfiguration\MessagingCommands\MessagingCommandsModule;

interface ConsoleCommandRunner
{
    #[MessageGateway(MessagingCommandsModule::ECOTONE_EXECUTE_CONSOLE_COMMAND_EXECUTOR)]
    public function execute(#[Header(MessagingCommandsModule::ECOTONE_CONSOLE_COMMAND_NAME)] $commandName, #[Payload] $parameters): mixed;
}
