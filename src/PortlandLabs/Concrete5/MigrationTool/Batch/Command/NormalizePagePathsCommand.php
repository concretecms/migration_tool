<?php

namespace PortlandLabs\Concrete5\MigrationTool\Batch\Command;


use Concrete\Core\Foundation\Command\Command;

class NormalizePagePathsCommand extends Command
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

    /**
     * @param mixed $batchId
     */
    public function setBatchId($batchId)
    {
        $this->batchId = $batchId;
    }



}