<?php
namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler;

use Concrete\Core\Page\Stack\Stack;
use Doctrine\ORM\EntityManagerInterface;
use PortlandLabs\Concrete5\MigrationTool\Batch\BatchInterface;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\AbstractStack;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\GlobalArea as ImportGlobalArea;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\StackFolder as ImportFolder;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\Stack as ImportStack;
use PortlandLabs\Concrete5\MigrationTool\Publisher\Logger\LoggerInterface;
use PortlandLabs\Concrete5\MigrationTool\Publisher\Service\StackTrait;
use Concrete\Core\Page\Stack\Folder\Folder;


defined('C5_EXECUTE') or die("Access Denied.");

/**
 * @property \PortlandLabs\Concrete5\MigrationTool\Publisher\Command\CreateStackStructureCommand $command
 */
class CreateStackStructureCommandHandler extends AbstractPageCommandHandler
{
    use StackTrait;

    /**
     * {@inheritdoc}
     *
     * @see \PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler\AbstractPageCommandHandler::getPage()
     *
     * @return \PortlandLabs\Concrete5\MigrationTool\Entity\Import\AbstractStack|null
     */
    public function getPage($id)
    {
        $em = app(EntityManagerInterface::class);

        return $em->find(AbstractStack::class, $id);
    }

    /**
     * {@inheritdoc}
     *
     * @see \PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler\AbstractHandler::execute()
     */
    public function execute(BatchInterface $batch, LoggerInterface $logger)
    {
        $stack = $this->getPage($this->command->getPageId());
        $logger->logPublishStarted($stack);
        if ($stack instanceof ImportFolder) {
            $new = $this->importFolder($stack, $batch);
        } elseif ($stack instanceof ImportGlobalArea) {
            $new = $this->importGlobalArea($stack, $batch);
        } elseif ($stack instanceof ImportStack) {
            $new = $this->importStack($stack, $batch);
        } else {
            $new = null;
        }
        if ($new === null) {
            $logger->logSkipped($stack);
        } else {
            $logger->logPublishComplete($stack, $new);
        }
    }

    private function importFolder(ImportFolder $folder, BatchInterface $batch): ?Folder
    {
        $name = (string) $folder->getName();
        if ($name === '') {
            return null;
        }
        $path = '/' . trim($folder->getPath() ?? '', '/');
        $folderPath = rtrim($path, '/') . '/' . $name;
        $existingFolders = $this->getExistingFolders();
        if (array_key_exists($folderPath, $existingFolders)) {
            return null;
        }
        $parentFolder = $this->getOrCreateFolderByPath($path);

        return $this->createFolder($name, $folderPath, $parentFolder);
    }

    private function importGlobalArea(ImportGlobalArea $globalArea, BatchInterface $batch): ?Stack
    {
        $name = (string) $globalArea->getName();
        if ($name === '') {
            return null;
        }
        if (Stack::getByName($name, 'RECENT', $batch->getSite())) {
            return null;
        }

        return Stack::addGlobalArea($name, $batch->getSite());
    }

    private function importStack(ImportStack $stack, BatchInterface $batch): ?Stack
    {
        $name = (string) $stack->getName();
        if ($name === '') {
            return null;
        }
        $parent = $this->getOrCreateFolderByPath($stack->getPath() ?? '');
        if ($this->getStackIDByName($name, $parent) !== null) {
            return null;
        }

        return Stack::addStack($name, $parent);
    }
}
