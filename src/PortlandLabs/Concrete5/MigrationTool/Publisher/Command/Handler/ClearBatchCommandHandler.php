<?php
namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler;


use PortlandLabs\Concrete5\MigrationTool\Batch\BatchInterface;
use PortlandLabs\Concrete5\MigrationTool\Publisher\Logger\LoggerInterface;
use Concrete\Core\Page\Page as CCMPage;

class ClearBatchCommandHandler extends AbstractHandler
{
    /**
     * Has the batch already been created? If so, we move to trash.
     *
     * {@inheritdoc}
     *
     * @see \PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler\AbstractHandler::execute()
     */
    public function execute(BatchInterface $batch, LoggerInterface $logger)
    {
        $orphaned = CCMPage::getByPath('/!import_batches/' . $batch->getId(), 'RECENT', $batch->getSite());
        if ($orphaned && !$orphaned->isError()) {
            $orphaned->moveToTrash();
        }
    }
}
