<?php
namespace Concrete\Package\MigrationTool\Backup\ContentImporter\ValueInspector\Item;

use Concrete\Core\Backup\ContentImporter\ValueInspector\Item\PageItem;
use Concrete\Core\Page\Page;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\Batch;

class BatchPageItem extends PageItem
{

    protected $batch;

    public function __construct(Batch $batch, $reference)
    {
        $this->batch = $batch;
        parent::__construct($reference);
    }

    public function getContentObject()
    {

        // First, does the passed path exist in the site? If so, we consider this solved
        $cPath = $this->getReference();
        $site = $this->batch->getSite();
        if ($cPath == '/' || $cPath == '') {
            if ($site) {
                return $site->getSiteHomePageObject('ACTIVE');
            } else {
                return Page::getByID(Page::getHomePageID(), 'ACTIVE');
            }
        }

        $c = Page::getByPath($cPath, 'ACTIVE', $site);
        if (is_object($c) && !$c->isError()) {
            return $c;
        }

        $entityManager = \ORM::entityManager();

        // Now, let's check to see if the path matches the original path of a page in the batch
        // This is kind of lame. First we grab the most recent log.
        $query = $entityManager->createQuery(
            'select l from \PortlandLabs\Concrete5\MigrationTool\Entity\Publisher\Log\Log l where l.batch_id = :batch_id order by l.date_started desc'
        );
        $query->setParameter('batch_id', $this->batch->getID());
        $query->setMaxResults(1);
        $log = $query->getOneOrNullResult();
        if ($log) {
            $query = $entityManager->createQuery('select p from
\PortlandLabs\Concrete5\MigrationTool\Entity\Publisher\Log\Object\Page p
inner join p.entry e
inner join e.log l where l.id = :log_id and p.cID > 0 and p.batch_path = :batch_path'
            );
            $query->setParameter('log_id', $log->getID());
            $query->setParameter('batch_path', $cPath);
            $query->setMaxResults(1);
            $loggedPage = $query->getOneOrNullResult();
            if (is_object($loggedPage)) {
                return Page::getByID($loggedPage->getPublishedPageID(), 'ACTIVE');
            }
        }
    }

}
