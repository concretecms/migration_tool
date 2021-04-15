<?php

namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Command;

use PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler\CreateThemesCommandHandler;

class CreateThemesCommand extends PublisherCommand
{

    public static function getHandler(): string
    {
        return CreateThemesCommandHandler::class;
    }



}