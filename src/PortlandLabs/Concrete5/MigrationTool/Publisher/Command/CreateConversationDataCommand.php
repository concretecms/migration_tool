<?php

namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Command;

use PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler\CreateConversationDataCommandHandler;

class CreateConversationDataCommand extends PublisherCommand
{

    public static function getHandler(): string
    {
        return CreateConversationDataCommandHandler::class;
    }


}