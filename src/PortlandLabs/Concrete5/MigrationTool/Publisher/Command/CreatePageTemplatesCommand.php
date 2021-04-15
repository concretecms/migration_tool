<?php

namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Command;

use PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler\CreatePageTemplatesCommandHandler;

class CreatePageTemplatesCommand extends PublisherCommand
{

    public static function getHandler(): string
    {
        return CreatePageTemplatesCommandHandler::class;
    }



}