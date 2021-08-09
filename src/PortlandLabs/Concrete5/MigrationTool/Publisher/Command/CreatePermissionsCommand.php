<?php

namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Command;

use PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler\CreatePermissionsCommandHandler;

class CreatePermissionsCommand extends PublisherCommand
{

    public static function getHandler(): string
    {
        return CreatePermissionsCommandHandler::class;
    }



}