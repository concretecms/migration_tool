<?php
namespace PortlandLabs\Concrete5\MigrationTool\Exporter\Item\Type;

use Concrete\Core\Entity\Board\Template;
use Doctrine\ORM\EntityManager;
use PortlandLabs\Concrete5\MigrationTool\Entity\Export\ExportItem;
use PortlandLabs\Concrete5\MigrationTool\Entity\Export\ObjectCollection;
use Symfony\Component\HttpFoundation\Request;
use Concrete\Core\Entity\Board\Board as BoardEntity;

defined('C5_EXECUTE') or die("Access Denied.");

class BoardTemplate extends AbstractType
{
    public function getHeaders()
    {
        return array(t('Name'));
    }

    public function exportCollection(ObjectCollection $collection, \SimpleXMLElement $element)
    {
        $em = app(EntityManager::class);
        $node = $element->addChild('boardtemplates');
        foreach ($collection->getItems() as $board) {
            $templateEntity = $em->find(Template::class, $board->getItemIdentifier());
            if ($templateEntity) {
                $template = $node->addChild('template');
                $template->addAttribute('icon', $templateEntity->getIcon());
                $template->addAttribute('handle', $templateEntity->getHandle());
                $template->addAttribute('name', $templateEntity->getName());
            }
        }
    }

    public function getResultColumns(ExportItem $exportItem)
    {
        $em = app(EntityManager::class);
        $template = $em->find(Template::class, $exportItem->getItemIdentifier());
        return [$template->getName()];
    }

    public function getItemsFromRequest($array)
    {
        $items = array();
        $em = app(EntityManager::class);
        foreach ($array as $id) {
            $template = $em->find(Template::class, $id);
            if (is_object($template)) {
                $exportTemplate = new \PortlandLabs\Concrete5\MigrationTool\Entity\Export\BoardTemplate();
                $exportTemplate->setItemId($template->getId());
                $items[] = $exportTemplate;
            }
        }

        return $items;
    }

    public function getResults(Request $request)
    {
        $em = app(EntityManager::class);
        $r = $em->getRepository(Template::class);
        $templates = $r->findAll(array(), array('name' => 'asc'));
        $items = array();
        foreach ($templates as $t) {
            $item = new \PortlandLabs\Concrete5\MigrationTool\Entity\Export\BoardTemplate();
            $item->setItemId($t->getId());
            $items[] = $item;
        }

        return $items;
    }

    public function getHandle()
    {
        return 'board_template';
    }

    public function getPluralDisplayName()
    {
        return t('Board Templates');
    }
}
