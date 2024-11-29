<?php
namespace PortlandLabs\Concrete5\MigrationTool\Exporter\Item\Type;

use Concrete\Core\Database\Connection\Connection;
use Concrete\Core\Page\PageList;
use PortlandLabs\Concrete5\MigrationTool\Entity\Export\ObjectCollection;
use Symfony\Component\HttpFoundation\Request;

defined('C5_EXECUTE') or die("Access Denied.");

class Page extends SinglePage
{
    public function exportCollection(ObjectCollection $collection, \SimpleXMLElement $element)
    {
        $node = $element->addChild('pages');
        foreach ($collection->getItems() as $page) {
            $c = \Page::getByID($page->getItemIdentifier());
            if (is_object($c) && !$c->isError()) {
                $this->exporter->export($c, $node);
            }
        }
    }

    public function getResults(Request $request)
    {
        $pl = new PageList();
        $query = $request->query;

        $keywords = $query->get('keywords');
        $ptID = $query->getInt('ptID');
        $startingPoint = $query->getInt('startingPoint');
        $datetime = \Core::make('helper/form/date_time')->translate('datetime', $query->all());
        $includeSystemPages = $query->get('includeSystemPages');
        $includeAliases = $query->get('includeAliases');

        $pl->ignorePermissions();
        if ($startingPoint) {
            $parent = \Page::getByID($startingPoint, 'ACTIVE');
            $pl->filterByPath($parent->getCollectionPath());
            $siteTree = $parent->getSiteTreeObject();
        } else {
            $siteTree = app('site')->getActiveSiteForEditing()->getSiteTreeObject();
        }
        $pl->setSiteTreeObject($siteTree);
        if ($datetime) {
            $pl->filterByPublicDate($datetime, '>=');
        }
        if ($ptID) {
            $pl->filterByPageTypeID($ptID);
        }
        if ($keywords) {
            $pl->filterByKeywords($keywords);
        }
        if ($includeSystemPages) {
            $pl->includeSystemPages();
        }
        if ($includeAliases) {
            $pl->includeAliases();
        }

        $pl->setItemsPerPage(1000);
        $results = $pl->getResults();
        $itemIDs = array();
        if (isset($parent) && !$parent->isError()) {
            $itemIDs[] = (int) $parent->getCollectionID();
        }
        foreach ($results as $c) {
            $cID = $includeAliases ? $c->getCollectionPointerOriginalID() : 0;
            $itemIDs[] = (int) ($cID ?: $c->getCollectionID());
        }
        if ($query->get('includeExternalLinks')) {
            foreach ($this->listExternalLinks($keywords, $parent) as $cID) {
                $itemIDs[] = $cID;
            }
        }

        return array_map(
            static function ($cID) {
                $item = new \PortlandLabs\Concrete5\MigrationTool\Entity\Export\Page();
                $item->setItemId($cID);
                return $item;
            },
            array_values(array_unique($itemIDs))
        );
    }

    public function getHandle()
    {
        return 'page';
    }

    public function getPluralDisplayName()
    {
        return t('Pages');
    }

    /**
     * @param string $keywords
     * @param \Concrete\Core\Page\Page|null $parent
     *
     * @return \Generator<int>
     */
    private function listExternalLinks($keywords, $parent = null)
    {
        $cn = app(Connection::class);
        $qb = $cn->createQueryBuilder();
        $qb
            ->select('p.cID')
            ->from('Pages', 'p')
            ->andWhere("p.cPointerExternalLink IS NOT NULL AND p.cPointerExternalLink <> ''")
        ;
        $keywords = trim((string) $keywords);
        if ($keywords !== '') {
            $qb
                ->innerJoin('p', 'CollectionVersions', 'cv', 'p.cID = cv.cID')
                ->andWhere('cv.cvID = (SELECT MAX(cvID) FROM CollectionVersions WHERE cID = cv.cID)')
                ->andWhere('cv.cvName LIKE :keywords')
                ->setParameter('keywords', "%{$keywords}%")
            ;
        }
        $pathPrefix = $parent === null ? '' : ($parent->getCollectionPath() . '/');
        $rs = $qb->execute();
        while (($cID = $rs->fetchOne()) !== false) {
            $cID = (int) $cID;
            if ($pathPrefix !== '') {
                $externalLink = \Concrete\Core\Page\Page::getByID($cID, 'RECENT');
                $externalLinkPath = $externalLink->generatePagePath();
                if (strpos($externalLinkPath, $pathPrefix) !== 0) {
                    continue;
                }
            }
            yield $cID;
        }
    }
}
