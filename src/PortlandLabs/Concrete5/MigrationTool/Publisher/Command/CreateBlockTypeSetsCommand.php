<?php

namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Command;

use PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler\CreateBlockTypeSetsCommandHandler;

class CreateBlockTypeSetsCommand extends PublisherCommand
{

    public static function getHandler(): string
    {
        return CreateBlockTypeSetsCommandHandler::class;
    }



}