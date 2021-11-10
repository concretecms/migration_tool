<?php

namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Command;

use PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler\CreateAttributeSetsCommandHandler;

class CreateAttributeSetsCommand extends PublisherCommand
{

    public static function getHandler(): string
    {
        return CreateAttributeSetsCommandHandler::class;
    }


}