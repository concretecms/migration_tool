<?php
namespace PortlandLabs\Concrete5\MigrationTool\Publisher\ContentImporter\ValueInspector\InspectionRoutine;

use Concrete\Core\Backup\ContentImporter\ValueInspector\InspectionRoutine\PageRoutine;
use Concrete\Package\MigrationTool\Backup\ContentImporter\ValueInspector\Item\BatchPageItem;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\Batch;

class BatchPageRoutine extends PageRoutine
{
    /**
     * @var \PortlandLabs\Concrete5\MigrationTool\Entity\Import\Batch
     */
    protected $batch;

    public function __construct(Batch $batch)
    {
        $this->batch = $batch;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Backup\ContentImporter\ValueInspector\InspectionRoutine\PageRoutine::getItem()
     */
    public function getItem($identifier)
    {
        if ($identifier && !$this->batch->isPublishToSitemap()) {
            $prefix = "/!import_batches/{$this->batch->getID()}/";
            if (strpos($identifier, $prefix) === 0) {
                $identifier = substr($identifier, strlen($prefix) - 1);
            }
        }

        return new BatchPageItem($this->batch, $identifier);
    }
}
