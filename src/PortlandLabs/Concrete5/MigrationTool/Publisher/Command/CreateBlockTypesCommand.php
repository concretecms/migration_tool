<?php

namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Command;

use PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler\CreateBlockTypesCommandHandler;

class CreateBlockTypesCommand extends PublisherCommand
{

    public static function getHandler(): string
    {
        return CreateBlockTypesCommandHandler::class;
    }



}