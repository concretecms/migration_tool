<?php

namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Command;

use PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler\CreateExpressEntitiesCommandHandler;

class CreateExpressEntitiesCommand extends PublisherCommand
{

    public static function getHandler(): string
    {
        return CreateExpressEntitiesCommandHandler::class;
    }


}