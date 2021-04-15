<?php

namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Command;

use PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler\CreateExpressEntryCommandHandler;

class CreateExpressEntryCommand extends PublisherCommand
{

    public static function getHandler(): string
    {
        return CreateExpressEntryCommandHandler::class;
    }

    protected $entryId;

    public function __construct($batchId, $logId, $entryId)
    {
        parent::__construct($batchId, $logId);
        $this->entryId = $entryId;
    }

    /**
     * @return mixed
     */
    public function getEntryId()
    {
        return $this->entryId;
    }

    /**
     * @param mixed $entryId
     */
    public function setEntryId($entryId)
    {
        $this->entryId = $entryId;
    }



}