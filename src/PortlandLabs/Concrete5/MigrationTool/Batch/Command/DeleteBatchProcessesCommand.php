<?php

namespace PortlandLabs\Concrete5\MigrationTool\Batch\Command;

use Concrete\Core\Foundation\Command\Command;

class DeleteBatchProcessesCommand extends Command
{

    protected $batchId;

    public function __construct($batchId)
    {
        $this->batchId = $batchId;
    }

    /**
     * @return mixed
     */
    public function getBatchId()
    {
        return $this->batchId;
    }



}