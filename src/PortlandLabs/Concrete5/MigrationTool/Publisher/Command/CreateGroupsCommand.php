<?php

namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Command;

use PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler\CreateGroupsCommandHandler;

class CreateGroupsCommand extends PublisherCommand
{

    public static function getHandler(): string
    {
        return CreateGroupsCommandHandler::class;
    }


}