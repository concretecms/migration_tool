<?php
namespace PortlandLabs\Concrete5\MigrationTool\Batch\Formatter\Stack;

use PortlandLabs\Concrete5\MigrationTool\Batch\Formatter\AbstractTreeJsonFormatter;
use PortlandLabs\Concrete5\MigrationTool\Batch\Formatter\StyleSet\TreeJsonFormatter as StyleSetTreeJsonFormatter;

defined('C5_EXECUTE') or die("Access Denied.");

class TreeJsonFormatter extends AbstractTreeJsonFormatter
{
    public function jsonSerialize(): array
    {
        $response = [];

        foreach ($this->collection->getStacks() as $stack) {
            /** @var \PortlandLabs\Concrete5\MigrationTool\Entity\Import\AbstractStack $stack */
            $messages = $this->getValidationMessages($stack);
            $stackFormatter = $stack->getStackFormatter();
            $formatter = $messages->getFormatter();
            $node = new \stdClass();
            $node->title = h($stack->getName());
            switch ($stack->getType()) {
                case 'global_area':
                case 'stack':
                    if ($stack->getLocaleID() !== '') {
                        $node->title .= ' <span class="badge text-bg-secondary">' . h($stack->getLocaleID()) . '</span>'; 
                    }
                    break;
            }
            $node->stackType = $stack->getType();
            $node->pagePath = $stack->getPath();
            $node->icon = $stackFormatter->getIconClass();
            $node->nodetype = 'stack';
            $node->exists = $stack->getPublisherValidator()->skipItem();
            $node->extraClasses = 'migration-node-main';
            $node->id = $stack->getId();
            $node->statusClass = $formatter->getCollectionStatusIconClass();
            $this->addMessagesNode($node, $messages);
            $node->children = [];

            if ($messages->count()) {
                $messageHolderNode = new \stdClass();
                $messageHolderNode->icon = $messages->getFormatter()->getCollectionStatusIconClass();
                $messageHolderNode->title = t('Errors');
                $messageHolderNode->children = array();
                foreach ($messages as $m) {
                    $messageNode = new \stdClass();
                    $messageNode->icon = $m->getFormatter()->getIconClass();
                    $messageNode->title = $m->getFormatter()->output();
                    $messageHolderNode->children[] = $messageNode;
                }
                $node->children[] = $messageHolderNode;
            }

            if (count($stack->getBlocks()) > 0) {
                $holderNode = new \stdClass();
                $holderNode->icon = 'fa fa-cubes';
                $holderNode->title = t('Blocks');
                foreach ($stack->getBlocks() as $block) {
                    $value = $block->getBlockValue();
                    if (is_object($value)) {
                        $blockFormatter = $value->getFormatter();
                        $blockNode = $blockFormatter->getBatchTreeNodeJsonObject();
                        if ($styleSet = $block->getStyleSet()) {
                            $styleSetFormatter = new StyleSetTreeJsonFormatter($styleSet);
                            $blockNode->children[] = $styleSetFormatter->getBatchTreeNodeJsonObject();
                        }
                        $holderNode->children[] = $blockNode;
                    }
                }
                $node->children[] = $holderNode;
            }

            $response[] = $node;

        }

        return $response;
    }
}
