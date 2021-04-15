<?php

namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Command;

use PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler\CreateBannedWordsCommandHandler;

class CreateBannedWordsCommand extends PublisherCommand
{

    public static function getHandler(): string
    {
        return CreateBannedWordsCommandHandler::class;
    }



}