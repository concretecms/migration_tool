<?php

namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Command;

use PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler\CreateSitesCommandHandler;

class CreateSitesCommand extends PublisherCommand
{

    public static function getHandler(): string
    {
        return CreateSitesCommandHandler::class;
    }



}