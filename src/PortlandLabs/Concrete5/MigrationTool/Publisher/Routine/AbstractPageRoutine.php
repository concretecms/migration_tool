<?php
namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Routine;

use Closure;
use PortlandLabs\Concrete5\MigrationTool\Batch\BatchInterface;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\Batch;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\ObjectCollection;
use PortlandLabs\Concrete5\MigrationTool\Publisher\Logger\LoggerInterface;

defined('C5_EXECUTE') or die("Access Denied.");

abstract class AbstractPageRoutine implements RoutineInterface
{
    protected $batch;

    abstract public function getPageRoutineCommand(BatchInterface $batch, LoggerInterface $logger, $pageId);

    public function getPageCollection(BatchInterface $batch)
    {
        return $batch->getObjectCollection('page');
    }

    public function getPages(ObjectCollection $collection)
    {
        return $collection->getPages();
    }

    public function getPagesOrderedForImport(Batch $batch, ?Closure $customComparer = null): array
    {
        $collection = $this->getPageCollection($batch);
        if (!$collection) {
            return [];
        }
        $pages = iterator_to_array($this->getPages($collection));
        usort(
            $pages,
            static function ($a, $b) use ($customComparer): int {
                if ($customComparer !== null) {
                    $cmp = $customComparer($a, $b);
                    if ($cmp) {
                        return $cmp;
                    }
                }
                $pathA = trim((string) $a->getBatchPath(), '/');
                $pathB = trim((string) $b->getBatchPath(), '/');
                $numA = $pathA === '' ? -1 : substr_count($pathA, '/');
                $numB = $pathB === '' ? -1 : substr_count($pathB, '/');
                if ($numA !== $numB) {
                    return $numA - $numB;
                }
                return (int) $a->getPosition() - (int) $b->getPosition();
            }
        );

        return array_values($pages);
    }

    public function getPublisherCommands(BatchInterface $batch, LoggerInterface $logger)
    {
        $pages = $this->getPagesOrderedForImport($batch);

        if (!$pages) {
            return array();
        }

        // Now loop through all pages, and build them
        $actions = array();
        foreach ($pages as $page) {
            if (!$page->getPublisherValidator()->skipItem()) {
                $action = $this->getPageRoutineCommand($batch, $logger, $page->getId());
                $actions[] = $action;
            }
        }

        return $actions;
    }
}
