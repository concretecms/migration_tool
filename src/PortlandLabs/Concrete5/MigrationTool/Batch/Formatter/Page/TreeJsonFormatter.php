<?php
namespace PortlandLabs\Concrete5\MigrationTool\Batch\Formatter\Page;

use PortlandLabs\Concrete5\MigrationTool\Batch\Formatter\AbstractTreeJsonFormatter;

defined('C5_EXECUTE') or die("Access Denied.");

class TreeJsonFormatter extends AbstractTreeJsonFormatter
{
    public function jsonSerialize()
    {
        $response = array();
        foreach ($this->collection->getPages() as $page) {
            /** @var \PortlandLabs\Concrete5\MigrationTool\Entity\Import\Page $page */
            $messages = $this->getValidationMessages($page);
            $formatter = $messages->getFormatter();
            $node = new \stdClass();
            $node->title = h($page->getName());
            if ($page->getLocaleRoot() !== null) {
                $node->title .= ' <span class="badge text-bg-secondary">' . h(implode('_', $page->getLocaleRoot())) . '</span>';
            }
            switch ($page->getKind()) {
                case $page::KIND_ALIAS:
                    $node->icon = 'fas fa-sign-out-alt';
                    break;
                case $page::KIND_EXTERNAL_LINK:
                    $node->icon = 'fas fa-external-link-alt';
                    break;
            }
            $node->lazy = true;
            $node->nodetype = 'page';
            $node->extraClasses = 'migration-node-main';

            $publisherValidator = $page->getPublisherValidator();
            $skipItem = $publisherValidator->skipItem();
            if ($skipItem) {
                $node->extraClasses .= ' migration-item-skipped';
            }

            $node->id = $page->getId();
            $node->pagePath =  '/' . $page->getBatchPath();
            $node->pageType = $page->getType();
            $node->pageTemplate = $page->getTemplate();
            if (!$skipItem) {
                $node->statusClass = $formatter->getCollectionStatusIconClass();
            }
            $response[] = $node;
        }

        return $response;
    }
}
