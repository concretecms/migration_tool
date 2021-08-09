<?php

namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Command;

use PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler\CreatePageFeedsCommandHandler;

class CreatePageFeedsCommand extends PublisherCommand
{

    public static function getHandler(): string
    {
        return CreatePageFeedsCommandHandler::class;
    }

}