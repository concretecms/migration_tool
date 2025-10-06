<?php
namespace PortlandLabs\Concrete5\MigrationTool\Batch\Validator\Page\Content;

use Concrete\Core\Backup\ContentImporter\ValueInspector\Item\ItemInterface;
use PortlandLabs\Concrete5\MigrationTool\Batch\Validator\Message;
use PortlandLabs\Concrete5\MigrationTool\Batch\Validator\MessageCollection;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\Batch;

defined('C5_EXECUTE') or die("Access Denied.");

class PageItemValidator implements ValidatorInterface
{
    public function itemExists(ItemInterface $item, Batch $batch)
    {
        if (is_object($item->getContentObject())) {
            return true;
        }
        $itemPath = $this->normalizePath($item->getReference());
        foreach ($batch->getPages() as $page) {
            if ($this->normalizePath($page->getBatchPath()) === $itemPath) {
                return true;
            }
            if ($this->normalizePath($page->getOriginalPath()) === $itemPath) {
                return true;
            }
        }
    }

    public function addMissingItemMessage(ItemInterface $item, MessageCollection $messages)
    {
        $messages->add(
            new Message(t('Referenced page at path %s cannot be found in the site or in the current content batch.', $item->getReference()), Message::E_WARNING)
        );
    }

    private function normalizePath(?string $path): string
    {
        return '/' . ltrim($path ?? '', '/');
    }
}
