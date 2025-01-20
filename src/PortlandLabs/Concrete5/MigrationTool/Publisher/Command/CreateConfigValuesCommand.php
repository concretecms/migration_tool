<?php

namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Command;

use PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler\CreateConfigValuesCommandHandler;

class CreateConfigValuesCommand extends PublisherCommand
{

    public static function getHandler(): string
    {
        return CreateConfigValuesCommandHandler::class;
    }


}