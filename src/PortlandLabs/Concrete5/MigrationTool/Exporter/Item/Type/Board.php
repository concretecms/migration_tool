<?php
namespace PortlandLabs\Concrete5\MigrationTool\Exporter\Item\Type;

use Doctrine\ORM\EntityManager;
use PortlandLabs\Concrete5\MigrationTool\Entity\Export\ExportItem;
use PortlandLabs\Concrete5\MigrationTool\Entity\Export\ObjectCollection;
use Symfony\Component\HttpFoundation\Request;
use Concrete\Core\Entity\Board\Board as BoardEntity;

defined('C5_EXECUTE') or die("Access Denied.");

class Board extends AbstractType
{
    public function getHeaders()
    {
        return array(t('Name'));
    }

    public function exportCollection(ObjectCollection $collection, \SimpleXMLElement $element)
    {
        $em = app(EntityManager::class);
        $node = $element->addChild('boards');
        foreach ($collection->getItems() as $board) {
            $boardEntity = $em->find(BoardEntity::class, $board->getItemIdentifier());
            if ($boardEntity) {
                $this->exporter->export($boardEntity, $node);
            }
        }
    }

    public function getResultColumns(ExportItem $exportItem)
    {
        $em = app(EntityManager::class);
        $board = $em->find(BoardEntity::class, $exportItem->getItemIdentifier());
        return [$board->getBoardName()];
    }

    public function getItemsFromRequest($array)
    {
        $items = array();
        $em = app(EntityManager::class);
        foreach ($array as $id) {
            $board = $em->find(BoardEntity::class, $id);
            if (is_object($board)) {
                $exportBoard = new \PortlandLabs\Concrete5\MigrationTool\Entity\Export\Board();
                $exportBoard->setItemId($board->getBoardID());
                $items[] = $exportBoard;
            }
        }

        return $items;
    }

    public function getResults(Request $request)
    {
        $em = app(EntityManager::class);
        $r = $em->getRepository(BoardEntity::class);
        $boards = $r->findAll(array(), array('boardName' => 'asc'));
        $items = array();
        foreach ($boards as $b) {
            $item = new \PortlandLabs\Concrete5\MigrationTool\Entity\Export\Board();
            $item->setItemId($b->getBoardID());
            $items[] = $item;
        }

        return $items;
    }

    public function getHandle()
    {
        return 'board';
    }

    public function getPluralDisplayName()
    {
        return t('Boards');
    }
}
