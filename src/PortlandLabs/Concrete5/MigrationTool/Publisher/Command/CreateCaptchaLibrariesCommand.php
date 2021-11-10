<?php

namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Command;

use PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler\CreateCaptchaLibrariesCommandHandler;

class CreateCaptchaLibrariesCommand extends PublisherCommand
{

    public static function getHandler(): string
    {
        return CreateCaptchaLibrariesCommandHandler::class;
    }



}