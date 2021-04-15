<?php

namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Command;

use PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler\CreatePermissionCategoriesCommandHandler;

class CreatePermissionCategoriesCommand extends PublisherCommand
{

    public static function getHandler(): string
    {
        return CreatePermissionCategoriesCommandHandler::class;
    }



}