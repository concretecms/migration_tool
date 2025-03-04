<?php

namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Command;

use PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler\ClosePublisherLogCommandHandler;

class ClosePublisherLogCommand extends PublisherCommand
{

    public static function getHandler(): string
    {
        return ClosePublisherLogCommandHandler::class;
    }


}