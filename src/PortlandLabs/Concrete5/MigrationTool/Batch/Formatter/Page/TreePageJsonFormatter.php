<?php
namespace PortlandLabs\Concrete5\MigrationTool\Batch\Formatter\Page;

use PortlandLabs\Concrete5\MigrationTool\Batch\Formatter\StyleSet\TreeJsonFormatter as StyleSetTreeJsonFormatter;
use PortlandLabs\Concrete5\MigrationTool\Batch\Validator\BatchObjectValidatorSubject;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\Batch;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\Page;

defined('C5_EXECUTE') or die("Access Denied.");

class TreePageJsonFormatter implements \JsonSerializable
{
    /**
     * @var \PortlandLabs\Concrete5\MigrationTool\Entity\Import\Page
     */
    protected $page;

    /**
     * @var \PortlandLabs\Concrete5\MigrationTool\Entity\Import\Batch
     */
    protected $batch;

    public function __construct(Batch $batch, Page $page)
    {
        $this->page = $page;
        $this->batch = $batch;
    }

    public function jsonSerialize()
    {
        $collection = $this->page->getCollection();
        $nodes = $this->getPageDetailNodes();

        $validator = $collection->getRecordValidator($this->batch);
        $subject = new BatchObjectValidatorSubject($this->batch, $this->page);
        $result = $validator->validate($subject);
        $messages = $result->getMessages();
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
            $nodes[] = $messageHolderNode;
        }
        if ($this->page->getAttributes()->count()) {
            $attributeHolderNode = new \stdClass();
            $attributeHolderNode->icon = 'fa fa-cogs';
            $attributeHolderNode->title = t('Attributes');
            $attributeHolderNode->children = array();
            foreach ($this->page->getAttributes() as $attribute) {
                $value = $attribute->getAttribute()->getAttributeValue();
                if (is_object($value)) {
                    $attributeFormatter = $value->getFormatter();
                    $attributeNode = $attributeFormatter->getBatchTreeNodeJsonObject();
                    $attributeHolderNode->children[] = $attributeNode;
                }
            }
            $nodes[] = $attributeHolderNode;
        }
        if ($this->page->getAreas()->count()) {
            $areaHolderNode = new \stdClass();
            $areaHolderNode->icon = 'fa fa-code';
            $areaHolderNode->title = t('Areas');
            $areaHolderNode->children = array();
            foreach ($this->page->getAreas() as $area) {
                $areaNode = new \stdClass();
                $areaNode->icon = 'fa fa-cubes';
                $areaNode->title = $area->getName();
                if ($styleSet = $area->getStyleSet()) {
                    $styleSetFormatter = new StyleSetTreeJsonFormatter($styleSet);
                    $areaNode->children[] = $styleSetFormatter->getBatchTreeNodeJsonObject();
                }
                foreach ($area->getBlocks() as $block) {
                    $value = $block->getBlockValue();
                    if (is_object($value)) {
                        $blockFormatter = $value->getFormatter();
                        $blockNode = $blockFormatter->getBatchTreeNodeJsonObject();
                        if ($styleSet = $block->getStyleSet()) {
                            $styleSetFormatter = new StyleSetTreeJsonFormatter($styleSet);
                            $blockNode->children[] = $styleSetFormatter->getBatchTreeNodeJsonObject();
                        }

                        $areaNode->children[] = $blockNode;
                    }
                }
                $areaHolderNode->children[] = $areaNode;
            }
            $nodes[] = $areaHolderNode;
        }

        return $nodes;
    }

    private function getPageDetailNodes(): array
    {
        $nodes = [];
        switch ($this->page->getKind()) {
            case Page::KIND_ALIAS:
                $nodes[] = [
                    'icon' => 'far fa-dot-circle',
                    'title' => t('Alias Of'),
                    'itemvalue' => h($this->page->getTarget()),
                ];
                break;
            case Page::KIND_EXTERNAL_LINK:
                $nodes[] = [
                    'icon' => 'far fa-dot-circle',
                    'title' => t('URL'),
                    'itemvalue' => h($this->page->getTarget()) . ($this->page->isNewWindow() ? (' (' . t('new window') . ')' ) : ''),
                ];
                break;
        }
        foreach ($this->page->getAdditionalPaths() as $additionalPath) {
            $nodes[] = [
                'icon' => 'fas fa-link',
                'title' => t('Additional Path'),
                'itemvalue' => h('/' . $additionalPath->getPath()),
            ];
        }
        foreach ($this->page->getHRefLangs() as $hrefLang) {
            $nodes[] = [
                'icon' => 'fas fa-random',
                'title' => h(t('Map for %s', $hrefLang->getLocaleID())),
                'itemvalue' => h($hrefLang->getPathForLocale()),
            ];
        }
        if ($this->page->getDescription() !== '') {
            $nodes[] = [
                'icon' => 'fa fa-quote-left',
                'title' => t('Description'),
                'itemvalue' => h($this->page->getDescription()),
            ];
        }
        if ($this->page->getPublicDate()) {
            $nodes[] = [
                'icon' => 'fa fa-calendar',
                'title' => t('Date'),
                'itemvalue' => h($this->page->getPublicDate()),
            ];
        }
        
        return $nodes;
    }
}
