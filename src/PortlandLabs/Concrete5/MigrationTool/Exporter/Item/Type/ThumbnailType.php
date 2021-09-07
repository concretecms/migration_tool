<?php
namespace PortlandLabs\Concrete5\MigrationTool\Exporter\Item\Type;

use Doctrine\ORM\EntityManager;
use PortlandLabs\Concrete5\MigrationTool\Entity\Export\ExportItem;
use PortlandLabs\Concrete5\MigrationTool\Entity\Export\ObjectCollection;
use Symfony\Component\HttpFoundation\Request;
use Concrete\Core\File\Image\Thumbnail\Type\Type;

defined('C5_EXECUTE') or die("Access Denied.");

class ThumbnailType extends AbstractType
{
    public function getHeaders()
    {
        return array(t('Name'));
    }

    public function exportCollection(ObjectCollection $collection, \SimpleXMLElement $element)
    {
        $node = $element->addChild('thumbnailtypes');
        foreach ($collection->getItems() as $thumbnailType) {
            $type = Type::getByID($thumbnailType->getItemIdentifier());
            if ($type) {
                $type->export($node);
            }
        }
    }

    public function getResultColumns(ExportItem $exportItem)
    {
        $type = Type::getByID($exportItem->getItemIdentifier());
        return [$type->getName()];
    }

    public function getItemsFromRequest($array)
    {
        $items = array();
        foreach ($array as $id) {
            $type = Type::getByID($id);
            if (is_object($type)) {
                $exportType = new \PortlandLabs\Concrete5\MigrationTool\Entity\Export\ThumbnailType();
                $exportType->setItemId($type->getID());
                $items[] = $exportType;
            }
        }

        return $items;
    }

    public function getResults(Request $request)
    {
        $types = Type::getList();
        $items = array();
        foreach ($types as $t) {
            $item = new \PortlandLabs\Concrete5\MigrationTool\Entity\Export\ThumbnailType();
            $item->setItemId($t->getID());
            $items[] = $item;
        }

        return $items;
    }

    public function getHandle()
    {
        return 'thumbnail_type';
    }

    public function getPluralDisplayName()
    {
        return t('Thumbnail Types');
    }
}
