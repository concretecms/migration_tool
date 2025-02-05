<?php
namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Routine;

use Closure;
use PortlandLabs\Concrete5\MigrationTool\Batch\BatchInterface;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\Batch;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\AbstractStack;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\StackFolder;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\ObjectCollection;
use PortlandLabs\Concrete5\MigrationTool\Publisher\Command\PublishStackContentCommand;
use PortlandLabs\Concrete5\MigrationTool\Publisher\Logger\LoggerInterface;

defined('C5_EXECUTE') or die("Access Denied.");

class PublishStackContentRoutine extends AbstractPageRoutine
{
    /**
     * {@inheritdoc}
     *
     * @see \PortlandLabs\Concrete5\MigrationTool\Publisher\Routine\AbstractPageRoutine::getPageCollection()
     */
    public function getPageCollection(BatchInterface $batch)
    {
        return $batch->getObjectCollection('stack');
    }

    /**
     * {@inheritdoc}
     *
     * @see \PortlandLabs\Concrete5\MigrationTool\Publisher\Routine\AbstractPageRoutine::getPages()
     */
    public function getPages(ObjectCollection $collection)
    {
        return $collection->getStacks();
    }

    /**
     * {@inheritdoc}
     *
     * @see \PortlandLabs\Concrete5\MigrationTool\Publisher\Routine\AbstractPageRoutine::getPageRoutineCommand()
     */
    public function getPageRoutineCommand(BatchInterface $batch, LoggerInterface $logger, $pageId)
    {
        return new PublishStackContentCommand($batch->getId(), $logger->getLog()->getId(), $pageId);
    }

    /**
     * {@inheritdoc}
     *
     * @see \PortlandLabs\Concrete5\MigrationTool\Publisher\Routine\AbstractPageRoutine::getPagesOrderedForImport()
     */
    public function getPagesOrderedForImport(Batch $batch, ?Closure $customComparer = null): array
    {
        if ($customComparer === null) {
            $customComparer = static function (AbstractStack $a, AbstractStack $b): int {
                if ($a instanceof StackFolder) {
                    return $b instanceof StackFolder ? 0 : -1;
                }
                
                return $b instanceof StackFolder ? 1 : 0;
            };
        }

        return parent::getPagesOrderedForImport($batch, $customComparer);
    }
}
