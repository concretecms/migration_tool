<?php
namespace PortlandLabs\Concrete5\MigrationTool\Importer\CIF\Element;

use PortlandLabs\Concrete5\MigrationTool\Entity\Import\Area;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\Attribute;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\Batch;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\Block;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\PageAttribute;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\PageObjectCollection;
use PortlandLabs\Concrete5\MigrationTool\Importer\CIF\ElementParserInterface;
use PortlandLabs\Concrete5\MigrationTool\Importer\Sanitizer\PagePathSanitizer;
use Concrete\Core\Utility\Service\Xml;

defined('C5_EXECUTE') or die("Access Denied.");

class Page implements ElementParserInterface
{
    /**
     * @var \PortlandLabs\Concrete5\MigrationTool\Importer\CIF\Attribute\Value\Manager
     */
    protected $attributeImporter;

    /**
     * @var \PortlandLabs\Concrete5\MigrationTool\Importer\CIF\Block\Manager
     */
    protected $blockImporter;

    /**
     * @var \PortlandLabs\Concrete5\MigrationTool\Importer\CIF\Element\StyleSet
     */
    protected $styleSetImporter;

    /**
     * @var \PortlandLabs\Concrete5\MigrationTool\Importer\Sanitizer\PagePathSanitizer
     */
    protected $pathSanitizer;

    /**
     * @var \Concrete\Core\Utility\Service\Xml
     */
    protected $xmlService;

    /**
     * @var \SimpleXMLElement|null
     */
    protected $simplexml;

    public function __construct()
    {
        $this->attributeImporter = app('migration/manager/import/attribute/value');
        $this->blockImporter = app('migration/manager/import/cif_block');
        $this->styleSetImporter = new StyleSet();
        $this->pathSanitizer = app(PagePathSanitizer::class);
        $this->xmlService = app(Xml::class);
    }

    /**
     * @return bool
     */
    public function hasPageNodes()
    {
        return isset($this->simplexml->pages->page) || isset($this->simplexml->pages->alias) || isset($this->simplexml->pages->{'external-link'});
    }

    /**
     * @return \Traversable<\SimpleXMLElement>
     */
    public function getPageNodes()
    {
        $result = [];
        foreach ($this->simplexml->pages->children() as $child) {
            if (in_array($child->getName(), ['page', 'alias', 'external-link'], true)) {
                yield $child;
            }
        }
    }

    /**
     * {@inheritdoc}
     *
     * @see \PortlandLabs\Concrete5\MigrationTool\Importer\CIF\ElementParserInterface::getObjectCollection()
     */
    public function getObjectCollection(\SimpleXMLElement $element, Batch $batch)
    {
        $this->simplexml = $element;
        $i = 0;
        $collection = new PageObjectCollection();
        if ($this->hasPageNodes()) {
            foreach ($this->getPageNodes() as $node) {
                $page = $this->parsePage($node);
                $page->setPosition($i);
                ++$i;
                $collection->getPages()->add($page);
                $page->setCollection($collection);
            }
        }

        return $collection;
    }

    /**
     * @param \SimpleXMLElement $node
     *
     * @return \PortlandLabs\Concrete5\MigrationTool\Entity\Import\Page
     */
    protected function parsePage($node)
    {
        $page = new \PortlandLabs\Concrete5\MigrationTool\Entity\Import\Page();
        $page->setName((string) html_entity_decode($node['name']));
        $page->setOriginalPath($this->pathSanitizer->sanitize((string) $node['path']));
        $page->setPublicDate((string) $node['public-date']);
        if (isset($node['package'])) {
            $page->setPackage((string) $node['package']);
        }
        $page->setUser((string) $node['user']);
        switch ($node->getName()) {
            case 'alias':
                $this->parseAlias($page, $node);
                break;
            case 'external-link':
                $this->parseExternalLink($page, $node);
                break;
            case 'page':
            default:
                $this->parseRegularPage($page, $node);
                break;
        }

        return $page;
    }

    protected function parseAlias(\PortlandLabs\Concrete5\MigrationTool\Entity\Import\Page $page, \SimpleXMLElement $node): void
    {
        $page
            ->setKind($page::KIND_ALIAS)
            ->setTarget($this->pathSanitizer->sanitize((string) $node['original-path']))
        ;
        $this->parseAdditionalPaths($page, $node);
    }

    protected function parseExternalLink(\PortlandLabs\Concrete5\MigrationTool\Entity\Import\Page $page, \SimpleXMLElement $node): void
    {
        $page
            ->setKind($page::KIND_EXTERNAL_LINK)
            ->setTarget((string) $node['destination'])
            ->setNewWindow(
                method_exists($this->xmlService, 'getBool')
                ? $this->xmlService->getBool($node['new-window'])
                : filter_var((string) $node['new-window'], FILTER_VALIDATE_BOOLEAN)
            )
        ;
    }

    protected function parseRegularPage(\PortlandLabs\Concrete5\MigrationTool\Entity\Import\Page $page, \SimpleXMLElement $node): void
    {
        $page->setKind($page::KIND_REGULAR_PAGE);
        $page->setDescription((string) html_entity_decode($node['description']));
        if (isset($node->locale)) {
            $page->setLocaleRoot((string) $node->locale['language'], (string) $node->locale['country']);
        }
        $this->parseAttributes($page, $node);
        $page->setTemplate((string) $node['template']);
        $page->setType((string) $node['pagetype']);
        $this->parseAdditionalPaths($page, $node);
        $this->parseHRefLangs($page, $node);
        
        $this->parseAreas($page, $node);
    }

    protected function parseAttributes(\PortlandLabs\Concrete5\MigrationTool\Entity\Import\Page $page, \SimpleXMLElement $node)
    {
        if ($node->attributes->attributekey) {
            $i = 0;
            foreach ($node->attributes->attributekey as $keyNode) {
                $attribute = $this->parseAttribute($keyNode);
                $pageAttribute = new PageAttribute();
                $pageAttribute->setAttribute($attribute);
                $pageAttribute->setPage($page);
                $page->attributes->add($pageAttribute);
                ++$i;
            }
        }
    }

    protected function parseAdditionalPaths(\PortlandLabs\Concrete5\MigrationTool\Entity\Import\Page $page, \SimpleXMLElement $node)
    {
        if (!isset($node->{'additional-path'})) {
            return;
        }
        foreach ($node->{'additional-path'} as $pathNode) {
            $additionalPath = new \PortlandLabs\Concrete5\MigrationTool\Entity\Import\Page\AdditionalPath();
            $additionalPath
                ->setPage($page)
                ->setPath($this->pathSanitizer->sanitize((string) $pathNode['path']))
            ;
            $page->getAdditionalPaths()->add($additionalPath);
        }
    }

    protected function parseHRefLangs(\PortlandLabs\Concrete5\MigrationTool\Entity\Import\Page $page, \SimpleXMLElement $node)
    {
        if (!isset($node->hreflang) || !isset($node->hreflang->alternate)) {
            return;
        }
        foreach ($node->hreflang->alternate as $alternateNode) {
            $hreflang = new \PortlandLabs\Concrete5\MigrationTool\Entity\Import\Page\HRefLang();
            $hreflang
                ->setPage($page)
                ->setLocaleID((string) $alternateNode['locale'])
                ->setPathForLocale((string) $alternateNode['path'])
            ;
            $page->getHRefLangs()->add($hreflang);
        }
    }

    protected function parseAreas(\PortlandLabs\Concrete5\MigrationTool\Entity\Import\Page $page, \SimpleXMLElement $node)
    {
        if ($node->area) {
            foreach ($node->area as $areaNode) {
                $area = $this->parseArea($areaNode);
                $area->setPage($page);
                $page->areas->add($area);
            }
        }
    }

    protected function parseAttribute($node)
    {
        $attribute = new Attribute();
        $attribute->setHandle((string) $node['handle']);
        $value = $this->attributeImporter->driver()->parse($node);
        $attribute->setAttributeValue($value);

        return $attribute;
    }

    protected function parseBlock($node)
    {
        $block = new Block();
        $type = (string) $node['type'];
        $block->setType($type);
        $block->setName((string) $node['name']);
        $bFilename = (string) $node['custom-template'];
        if ($bFilename) {
            $block->setCustomTemplate($bFilename);
        }
        $block->setDefaultsOutputIdentifier((string) $node['mc-block-id']);
        if (isset($node->style)) {
            $styleSet = $this->styleSetImporter->import($node->style);
            $block->setStyleSet($styleSet);
        }
        $value = $this->blockImporter->driver('unmapped')->parse($node);
        $block->setBlockValue($value);

        return $block;
    }

    protected function parseArea($node)
    {
        $area = new Area();
        $area->setName((string) $node['name']);

        if (isset($node->style)) {
            $styleSet = $this->styleSetImporter->import($node->style);
            $area->setStyleSet($styleSet);
        }

        // Parse areas
        $nodes = false;
        if ($node->blocks->block) {
            $nodes = $node->blocks->block;
        } elseif ($node->block) {
            // 5.6
            $nodes = $node->block;
        }

        if ($nodes) {
            $i = 0;
            foreach ($nodes as $blockNode) {
                if ($blockNode['type']) {
                    $block = $this->parseBlock($blockNode);
                } elseif ($blockNode['mc-block-id'] != '') {
                    $block = new Block();
                    $block->setDefaultsOutputIdentifier((string) $blockNode['mc-block-id']);
                }
                if (isset($block)) {
                    $block->setPosition($i);
                    $block->setArea($area);
                    $area->blocks->add($block);
                    ++$i;
                }
            }
        }

        return $area;
    }
}
