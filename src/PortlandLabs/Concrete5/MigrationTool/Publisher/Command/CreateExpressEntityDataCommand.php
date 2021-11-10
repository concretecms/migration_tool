<?php

namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Command;

use PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler\CreateExpressEntityDataCommandHandler;

class CreateExpressEntityDataCommand extends PublisherCommand
{
    public static function getHandler(): string
    {
        return CreateExpressEntityDataCommandHandler::class;
    }



}