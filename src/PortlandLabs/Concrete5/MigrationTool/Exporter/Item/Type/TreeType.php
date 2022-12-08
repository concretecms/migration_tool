<?php
namespace PortlandLabs\Concrete5\MigrationTool\Exporter\Item\Type;

use Doctrine\ORM\EntityManager;
use PortlandLabs\Concrete5\MigrationTool\Entity\Export\ExportItem;
use PortlandLabs\Concrete5\MigrationTool\Entity\Export\ObjectCollection;
use Symfony\Component\HttpFoundation\Request;

defined('C5_EXECUTE') or die("Access Denied.");

class TreeType extends AbstractType
{
    public function getHeaders()
    {
        return array(t('Handle'));
    }

    public function exportCollection(ObjectCollection $collection, \SimpleXMLElement $element)
    {
        $node = $element->addChild('treetypes');
        foreach ($collection->getItems() as $treeType) {
            $type = \Concrete\Core\Tree\TreeType::getByID($treeType->getItemIdentifier());
            if ($type) {
                $type->export($node);
            }
        }
    }

    public function getResultColumns(ExportItem $exportItem)
    {
        $type = \Concrete\Core\Tree\TreeType::getByID($exportItem->getItemIdentifier());
        return [$type->getTreeTypeHandle()];
    }

    public function getItemsFromRequest($array)
    {
        $items = array();
        foreach ($array as $id) {
            $type = \Concrete\Core\Tree\TreeType::getByID($id);
            if (is_object($type)) {
                $exportType = new \PortlandLabs\Concrete5\MigrationTool\Entity\Export\TreeType();
                $exportType->setItemId($type->getTreeTypeID());
                $items[] = $exportType;
            }
        }

        return $items;
    }

    public function getResults(Request $request)
    {
        $types = \Concrete\Core\Tree\TreeType::getList();
        $items = array();
        foreach ($types as $t) {
            $item = new \PortlandLabs\Concrete5\MigrationTool\Entity\Export\TreeType();
            $item->setItemId($t->getTreeTypeID());
            $items[] = $item;
        }

        return $items;
    }

    public function getHandle()
    {
        return 'tree_type';
    }

    public function getPluralDisplayName()
    {
        return t('Tree Types');
    }
}
