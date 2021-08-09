<?php

namespace PortlandLabs\Concrete5\MigrationTool\Batch\Command;

use Concrete\Core\Foundation\Command\Command;

class MapContentTypesCommand extends Command
{

    protected $batchId;

    protected $mapper;

    protected $item;

    public function __construct($batchId, $mapper, $item)
    {
        $this->batchId = $batchId;
        $this->mapper = $mapper;
        $this->item = $item;
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

    /**
     * @return mixed
     */
    public function getMapper()
    {
        return $this->mapper;
    }

    /**
     * @param mixed $mapper
     */
    public function setMapper($mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * @return mixed
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @param mixed $item
     */
    public function setItem($item)
    {
        $this->item = $item;
    }

    public function getBatchHandle(): string
    {
        return 'map_content_types';
    }


}