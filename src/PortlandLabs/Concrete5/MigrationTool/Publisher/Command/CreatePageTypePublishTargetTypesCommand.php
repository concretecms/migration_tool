<?php

namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Command;

use PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler\CreatePageTypePublishTargetTypesCommandHandler;

class CreatePageTypePublishTargetTypesCommand extends PublisherCommand
{

    public static function getHandler(): string
    {
        return CreatePageTypePublishTargetTypesCommandHandler::class;
    }


}