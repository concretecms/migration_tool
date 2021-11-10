<?php

namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Command;

use PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler\CreateAttributeTypesCommandHandler;

class CreateAttributeTypesCommand extends PublisherCommand
{

    public static function getHandler(): string
    {
        return CreateAttributeTypesCommandHandler::class;
    }


}