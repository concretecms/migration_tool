<?php
namespace PortlandLabs\Concrete5\MigrationTool\Importer\CIF\Element;

use PortlandLabs\Concrete5\MigrationTool\Entity\Import\Batch;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\SinglePageObjectCollection;

defined('C5_EXECUTE') or die("Access Denied.");

class SinglePage extends Page
{
    protected $pages = array();

    /**
     * {@inheritdoc}
     *
     * @see \PortlandLabs\Concrete5\MigrationTool\Importer\CIF\Element\Page::hasPageNodes()
     */
    public function hasPageNodes()
    {
        return isset($this->simplexml->singlepages->page);
    }

    /**
     * {@inheritdoc}
     *
     * @see \PortlandLabs\Concrete5\MigrationTool\Importer\CIF\Element\Page::getPageNodes()
     */
    public function getPageNodes()
    {
        return $this->simplexml->singlepages->page;
    }
    
    public function getObjectCollection(\SimpleXMLElement $element, Batch $batch)
    {
        $this->simplexml = $element;
        $i = 0;
        $collection = new SinglePageObjectCollection();
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

    protected function parsePage($node)
    {
        $page = parent::parsePage($node);
        $page->setFilename((string) $node['filename']);
        if (isset($node['root']) && $node['root'] == true) {
            $page->setIsAtRoot(true);
        }
        if (isset($node['global']) && $node['global'] == true) {
            $page->setIsGlobal(true);
        }

        return $page;
    }
}
