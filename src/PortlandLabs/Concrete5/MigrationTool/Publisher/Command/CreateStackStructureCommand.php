<?php

namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Command;

use PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler\CreateStackStructureCommandHandler;

class CreateStackStructureCommand extends AbstractPagePublisherCommand
{

    public static function getHandler(): string
    {
        return CreateStackStructureCommandHandler::class;
    }



}