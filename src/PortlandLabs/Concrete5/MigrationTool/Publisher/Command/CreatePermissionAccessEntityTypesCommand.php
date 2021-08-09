<?php

namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Command;

use PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler\CreatePermissionAccessEntityTypesCommandHandler;

class CreatePermissionAccessEntityTypesCommand extends PublisherCommand
{

    public static function getHandler(): string
    {
        return CreatePermissionAccessEntityTypesCommandHandler::class;
    }



}