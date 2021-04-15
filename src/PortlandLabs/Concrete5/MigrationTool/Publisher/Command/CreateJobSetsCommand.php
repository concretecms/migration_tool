<?php

namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Command;

use PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler\CreateJobSetsCommandHandler;

class CreateJobSetsCommand extends PublisherCommand
{

    public static function getHandler(): string
    {
        return CreateJobSetsCommandHandler::class;
    }



}