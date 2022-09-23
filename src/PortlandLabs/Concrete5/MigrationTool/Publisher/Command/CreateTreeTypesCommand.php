<?php

namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Command;

use PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler\CreateTreeTypesCommandHandler;

class CreateTreeTypesCommand extends PublisherCommand
{

    public static function getHandler(): string
    {
        return CreateTreeTypesCommandHandler::class;
    }



}