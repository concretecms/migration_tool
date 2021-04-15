<?php

namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Command;

use PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler\CreateWorkflowTypesCommandHandler;

class CreateWorkflowTypesCommand extends PublisherCommand
{
    public static function getHandler(): string
    {
        return CreateWorkflowTypesCommandHandler::class;
    }



}