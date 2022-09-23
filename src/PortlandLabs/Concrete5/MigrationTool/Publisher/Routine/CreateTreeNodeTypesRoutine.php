<?php
namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Routine;

use PortlandLabs\Concrete5\MigrationTool\Publisher\Command\CreateTreeNodeTypesCommand;

defined('C5_EXECUTE') or die("Access Denied.");

class CreateTreeNodeTypesRoutine extends AbstractRoutine
{

    public function getCommandClass()
    {
        return CreateTreeNodeTypesCommand::class;
    }

}
