<?php

namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Command;

use PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler\CreateThumbnailTypesCommandHandler;

class CreateThumbnailTypesCommand extends PublisherCommand
{

    public static function getHandler(): string
    {
        return CreateThumbnailTypesCommandHandler::class;
    }



}