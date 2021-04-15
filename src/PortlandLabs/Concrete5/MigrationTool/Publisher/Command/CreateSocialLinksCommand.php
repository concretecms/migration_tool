<?php

namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Command;

use PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler\CreateSocialLinksCommandHandler;

class CreateSocialLinksCommand extends PublisherCommand
{

    public static function getHandler(): string
    {
        return CreateSocialLinksCommandHandler::class;
    }



}