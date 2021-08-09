<?php

namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Command;

use PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler\CreatePageTypeComposerControlTypesCommandHandler;

class CreatePageTypeComposerControlTypesCommand extends PublisherCommand
{

    public static function getHandler(): string
    {
        return CreatePageTypeComposerControlTypesCommandHandler::class;
    }



}