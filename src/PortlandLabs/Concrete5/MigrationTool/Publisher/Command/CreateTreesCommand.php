<?php

namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Command;

use PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler\CreateTreesCommandHandler;

class CreateTreesCommand extends PublisherCommand
{

    public static function getHandler(): string
    {
        return CreateTreesCommandHandler::class;
    }


}