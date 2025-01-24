<?php
namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler;

use Concrete\Core\Page\Type\Type;
use Concrete\Core\Utility\Service\Text;
use Doctrine\ORM\EntityManagerInterface;
use PortlandLabs\Concrete5\MigrationTool\Batch\BatchInterface;
use PortlandLabs\Concrete5\MigrationTool\Batch\ContentMapper\TargetItemList;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\Batch;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\Page;

defined('C5_EXECUTE') or die("Access Denied.");

abstract class AbstractPageCommandHandler extends AbstractHandler
{

    protected function getPageByPath(Batch $batch, $path)
    {
        $path = trim((string) $path, '/');
        return \Page::getByPath(
            '/!import_batches/' . $batch->getID() . ($path === '' ? '' : "/{$path}"),
            'RECENT',
            $batch->getSite()->getSiteTreeObject()
        );
    }

    /**
     * @return \PortlandLabs\Concrete5\MigrationTool\Entity\Import\Page|null
     */
    public function getPage($id)
    {
        $entityManager = app(EntityManagerInterface::class);

        return $entityManager->find(\PortlandLabs\Concrete5\MigrationTool\Entity\Import\Page::class, $id);
    }

    protected function ensureParentPageExists(Batch $batch, Page $page)
    {
        $service = new Text();
        $path = trim($page->getBatchPath(), '/');
        $paths = explode('/', $path);
        $batchParent = $this->getBatchParentPage($batch);
        $parent = $batchParent;
        $prefix = '';

        array_pop($paths);

        foreach($paths as $path) {
            $currentPath = $prefix . $path;
            $c = \Concrete\Core\Page\Page::getByPath($batchParent->getCollectionPath() . '/' . $currentPath, 'RECENT', $batch->getSite()->getSiteTreeObject());
            if ($c->isError() && $c->getError() == COLLECTION_NOT_FOUND) {
                $data = array();
                $data['cHandle'] = $path;
                $data['name'] = $service->unhandle($data['handle']);
                $data['uID'] = USER_SUPER_ID;
                $parent = $parent->add(null, $data);
            } else {
                $parent = $c;
            }
            $prefix = $currentPath . '/';
        }
        return $parent;
    }

    public function getTargetItem($batch, $mapper, $subject)
    {
        return TargetItemList::getBatchTargetItem($batch, $mapper, $subject);
    }

    protected function getBatchParentPage(BatchInterface $batch)
    {
        $page = \Page::getByPath('/!import_batches/' . $batch->getID(), 'RECENT', $batch->getSite()->getSiteTreeObject());
        if (is_object($page) && !$page->isError()) {
            return $page;
        } else {
            return $this->addBatchParent($batch);
        }
    }

    protected function addBatchParent(BatchInterface $batch)
    {
        $holder = \Page::getByPath('/!import_batches', 'RECENT', $batch->getSite()->getSiteTreeObject());
        $type = Type::getByHandle('import_batch');
        return $holder->add($type, array(
            'cName' => $batch->getID(),
            'pkgID' => \Package::getByHandle('migration_tool')->getPackageID(),
        ));

    }
}
