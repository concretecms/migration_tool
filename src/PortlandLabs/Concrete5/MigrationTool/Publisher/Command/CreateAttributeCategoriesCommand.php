<?php

namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Command;

use PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler\CreateAttributeCategoriesCommandHandler;

class CreateAttributeCategoriesCommand extends PublisherCommand
{

    public static function getHandler(): string
    {
        return CreateAttributeCategoriesCommandHandler::class;
    }


}