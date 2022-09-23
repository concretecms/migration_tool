<?php
namespace PortlandLabs\Concrete5\MigrationTool\Exporter\Item\Type;

use Concrete\Core\Tree\Node\NodeType;
use Doctrine\ORM\EntityManager;
use PortlandLabs\Concrete5\MigrationTool\Entity\Export\ExportItem;
use PortlandLabs\Concrete5\MigrationTool\Entity\Export\ObjectCollection;
use Symfony\Component\HttpFoundation\Request;

defined('C5_EXECUTE') or die("Access Denied.");

class TreeNodeType extends AbstractType
{
    public function getHeaders()
    {
        return array(t('Handle'));
    }

    public function exportCollection(ObjectCollection $collection, \SimpleXMLElement $element)
    {
        $node = $element->addChild('treenodetypes');
        foreach ($collection->getItems() as $treeType) {
            $type = NodeType::getByID($treeType->getItemIdentifier());
            if ($type) {
                $type->export($node);
            }
        }
    }

    public function getResultColumns(ExportItem $exportItem)
    {
        $type = NodeType::getByID($exportItem->getItemIdentifier());
        return [$type->getTreeNodeTypeHandle()];
    }

    public function getItemsFromRequest($array)
    {
        $items = array();
        foreach ($array as $id) {
            $type = NodeType::getByID($id);
            if (is_object($type)) {
                $exportType = new \PortlandLabs\Concrete5\MigrationTool\Entity\Export\TreeNodeType();
                $exportType->setItemId($type->getTreeNodeTypeID());
                $items[] = $exportType;
            }
        }

        return $items;
    }

    public function getResults(Request $request)
    {
        $types = NodeType::getList();
        $items = array();
        foreach ($types as $t) {
            $item = new \PortlandLabs\Concrete5\MigrationTool\Entity\Export\TreeNodeType();
            $item->setItemId($t->getTreeNodeTypeID());
            $items[] = $item;
        }

        return $items;
    }

    public function getHandle()
    {
        return 'tree_node_type';
    }

    public function getPluralDisplayName()
    {
        return t('Tree Node Types');
    }
}
