<?php

namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Command;

use PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler\CreateThumbnailTypesCommandHandler;
use PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler\CreateTreeNodeTypesCommandHandler;

class CreateTreeNodeTypesCommand extends PublisherCommand
{

    public static function getHandler(): string
    {
        return CreateTreeNodeTypesCommandHandler::class;
    }



}