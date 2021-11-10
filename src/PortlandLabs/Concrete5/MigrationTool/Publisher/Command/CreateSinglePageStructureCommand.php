<?php

namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Command;

use PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler\CreateSinglePageStructureCommandHandler;

class CreateSinglePageStructureCommand extends AbstractPagePublisherCommand
{

    public static function getHandler(): string
    {
        return CreateSinglePageStructureCommandHandler::class;
    }


}