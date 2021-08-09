<?php

namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Command;

use PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler\CreatePackagesCommandHandler;

class CreatePackagesCommand extends PublisherCommand
{

    public static function getHandler(): string
    {
        return CreatePackagesCommandHandler::class;
    }

}