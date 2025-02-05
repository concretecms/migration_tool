<?php
namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler;

use Concrete\Core\Page\Page as CCMPage;
use Doctrine\ORM\EntityManagerInterface;
use PortlandLabs\Concrete5\MigrationTool\Batch\ContentMapper\TargetItemList;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\Batch;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\Page as MTPage;


defined('C5_EXECUTE') or die("Access Denied.");

abstract class AbstractPageCommandHandler extends AbstractHandler
{
    protected function getPageByPath(Batch $batch, ?string $path): ?CCMPage
    {
        if ($batch->isPublishToSitemap()) {
            $fullPath = '';
        } else {
            $fullPath = '/!import_batches/' . $batch->getID();
        }
        $path = trim((string) $path, '/');
        if ($path !== '') {
            $fullPath .= '/' . $path;
        }
        if ($fullPath === '') {
            $site = $batch->getSite();
            $ccmPage = $site ? $site->getSiteHomePageObject() : null;
        } else {
            $ccmPage = CCMPage::getByPath($fullPath, 'RECENT', $batch->getSite());
        }

        return $ccmPage && !$ccmPage->isError() ? $ccmPage : null;
    }

    public function getPage(?string $id): ?object
    {
        if (empty($id)) {
            return null;
        }
        $entityManager = app(EntityManagerInterface::class);

        return $entityManager->find(MTPage::class, $id);
    }

    public function getTargetItem($batch, $mapper, $subject)
    {
        return TargetItemList::getBatchTargetItem($batch, $mapper, $subject);
    }
}
