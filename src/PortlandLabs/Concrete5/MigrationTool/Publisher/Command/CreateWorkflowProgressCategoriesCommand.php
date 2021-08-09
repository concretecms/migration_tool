<?php

namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Command;

use PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler\CreateWorkflowProgressCategoriesCommandHandler;

class CreateWorkflowProgressCategoriesCommand extends PublisherCommand
{

    public static function getHandler(): string
    {
        return CreateWorkflowProgressCategoriesCommandHandler::class;
    }


}