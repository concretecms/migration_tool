<?php

namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Command;

use League\Tactician\Bernard\QueueableCommand;
use PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler\CreateConfigValuesCommandHandler;

class CreateConfigValuesCommand extends PublisherCommand
{

    public static function getHandler(): string
    {
        return CreateConfigValuesCommandHandler::class;
    }


}