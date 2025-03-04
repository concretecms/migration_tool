<?php
namespace PortlandLabs\Concrete5\MigrationTool\Batch;

interface BatchInterface
{
    public function getObjectCollections();

    public function getObjectCollection($collection);

    /**
     * @return \Concrete\Core\Entity\Site\Site|null
     */
    public function getSite();
}
