<?php
namespace PortlandLabs\Concrete5\MigrationTool\Importer\CIF\Element;

use PortlandLabs\Concrete5\MigrationTool\Entity\Import\Batch;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\TreeNodeTypeObjectCollection;
use PortlandLabs\Concrete5\MigrationTool\Importer\CIF\ElementParserInterface;

defined('C5_EXECUTE') or die("Access Denied.");

class TreeNodeType implements ElementParserInterface
{
    public function getObjectCollection(\SimpleXMLElement $element, Batch $batch)
    {
        $collection = new TreeNodeTypeObjectCollection();
        if ($element->treenodetypes->treenodetype) {
            foreach ($element->treenodetypes->treenodetype as $node) {
                $type = new \PortlandLabs\Concrete5\MigrationTool\Entity\Import\TreeNodeType();
                $type->setHandle((string) $node['handle']);
                $collection->getTypes()->add($type);
                $type->setCollection($collection);
            }
        }

        return $collection;
    }
}
