<?php

namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Command;

use PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler\PublishStackContentCommandHandler;

class PublishStackContentCommand extends AbstractPagePublisherCommand
{

    public static function getHandler(): string
    {
        return PublishStackContentCommandHandler::class;
    }

}