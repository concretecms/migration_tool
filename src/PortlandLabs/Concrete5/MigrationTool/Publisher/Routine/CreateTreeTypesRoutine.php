<?php
namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Routine;

use PortlandLabs\Concrete5\MigrationTool\Publisher\Command\CreateTreeTypesCommand;

defined('C5_EXECUTE') or die("Access Denied.");

class CreateTreeTypesRoutine extends AbstractRoutine
{

    public function getCommandClass()
    {
        return CreateTreeTypesCommand::class;
    }

}
