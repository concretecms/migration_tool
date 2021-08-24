<?php
namespace PortlandLabs\Concrete5\MigrationTool\Exporter\Item\Type;

use Concrete\Core\Captcha\Library;
use Concrete\Core\Sharing\SocialNetwork\Link;
use PortlandLabs\Concrete5\MigrationTool\Entity\Export\ExportItem;
use PortlandLabs\Concrete5\MigrationTool\Entity\Export\ObjectCollection;
use Symfony\Component\HttpFoundation\Request;

defined('C5_EXECUTE') or die("Access Denied.");

class SocialLink extends AbstractType
{
    public function getHeaders()
    {
        return array(t('Name'));
    }

    public function exportCollection(ObjectCollection $collection, \SimpleXMLElement $element)
    {
        $node = $element->addChild('sociallinks');
        foreach ($collection->getItems() as $link) {
            $socialLink = Link::getByServiceHandle($link->getItemIdentifier());
            if (is_object($socialLink)) {
                $this->exporter->export($socialLink, $node);
            }
        }
    }

    public function getResultColumns(ExportItem $exportItem)
    {
        $link = Link::getByServiceHandle($exportItem->getItemIdentifier());
        $return = array();
        if (is_object($link)) {
            $return[] = $link->getServiceHandle();
        }

        return $return;
    }

    public function getItemsFromRequest($array)
    {
        $items = array();
        foreach ($array as $id) {
            $link = Link::getByServiceHandle($id);
            if (is_object($link)) {
                $socialLink = new \PortlandLabs\Concrete5\MigrationTool\Entity\Export\SocialLink();
                $socialLink->setHandle($link->getServiceHandle());
                $items[] = $socialLink;
            }
        }

        return $items;
    }

    public function getResults(Request $request)
    {
        $list = Link::getList();
        $items = array();
        foreach ($list as $link) {
            $item = new \PortlandLabs\Concrete5\MigrationTool\Entity\Export\SocialLink();
            $item->setHandle($link->getServiceHandle());
            $items[] = $item;
        }

        return $items;
    }

    public function getHandle()
    {
        return 'social_link';
    }

    public function getPluralDisplayName()
    {
        return t('Social Links');
    }
}
