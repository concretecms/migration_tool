<?php

namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Command;

use PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler\CreateJobsCommandHandler;

class CreateJobsCommand extends PublisherCommand
{

    public static function getHandler(): string
    {
        return CreateJobsCommandHandler::class;
    }



}