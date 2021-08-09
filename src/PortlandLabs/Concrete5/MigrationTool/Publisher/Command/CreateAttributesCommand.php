<?php

namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Command;

use PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler\CreateAttributesCommandHandler;

class CreateAttributesCommand extends PublisherCommand
{

    public static function getHandler(): string
    {
        return CreateAttributesCommandHandler::class;
    }


}