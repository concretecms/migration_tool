<?php

namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler;

use Concrete\Core\Page\Page as CCMPage;
use Concrete\Core\Utility\Service\Text;
use Doctrine\ORM\EntityManagerInterface;
use PortlandLabs\Concrete5\MigrationTool\Batch\BatchInterface;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\Page as MTPage;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\Batch;
use PortlandLabs\Concrete5\MigrationTool\Publisher\Logger\LoggerInterface;
use Concrete\Core\Package\PackageService;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\PageObjectCollection;
use Concrete\Core\Error\UserMessageException;
use Concrete\Core\Page\Type\Type;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @property \PortlandLabs\Concrete5\MigrationTool\Publisher\Command\CreatePageStructureCommand $command
 */
class CreatePageStructureCommandHandler extends AbstractPageCommandHandler
{
    /**
     * @var \Concrete\Core\Page\Page|null
     */
    private $batchParentPage = null;

    /**
     * {@inheritdoc}
     *
     * @see \PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler\AbstractHandler::execute()
     */
    public function execute(BatchInterface $batch, LoggerInterface $logger)
    {
        $mtPage = $this->getPage($this->command->getPageId());
        // Let's be sure the batch parent page exists
        $this->getBatchParentPage($batch);
        $this->publishPage($batch, $logger, $mtPage);
    }

    private function publishPage(BatchInterface $batch, LoggerInterface $logger, MTPage $mtPage): CCMPage
    {
        $already = $this->getPageByPath($batch, $mtPage->getBatchPath());
        if ($already && !$already->isError()) {
            return $already;
        }
        $logger->logPublishStarted($mtPage);
        $ccmParentPage = $this->getParentPage($batch, $logger, $mtPage);
        switch ($mtPage->getKind()) {
            case MTPage::KIND_ALIAS:
                $ccmPage = $this->publishAliasPage($batch, $logger, $ccmParentPage, $mtPage);
                break;
            case MTPage::KIND_EXTERNAL_LINK:
                $ccmPage = $this->publishExternalLink($batch, $ccmParentPage, $mtPage);
                break;
            case MTPage::KIND_REGULAR_PAGE:
            default:
                $ccmPage = $this->publishRegularPage($batch, $ccmParentPage, $mtPage);
                break;
        }
        $logger->logPublishComplete($mtPage, $ccmPage);

        return $ccmPage;
    }

    private function publishRegularPage(BatchInterface $batch, CCMPage $ccmParentPage, MTPage $mtPage): CCMPage
    {
        $slugs = preg_split('{/}', (string) $mtPage->getBatchPath(), -1, PREG_SPLIT_NO_EMPTY);
        $data = [
            'uID' => $this->getUserID($batch, $mtPage->getUser()),
            'name' => $mtPage->getName(),
            'cDescription' => $mtPage->getDescription(),
            'cHandle' => array_pop($slugs) ?? '',
        ];
        $cDatePublic = $mtPage->getPublicDate();
        if ($cDatePublic) {
            $data['cDatePublic'] = $cDatePublic;
        }
        $type = $this->getTargetItem($batch, 'page_type', $mtPage->getType());
        if ($type) {
            $data['ptID'] = $type->getPageTypeID();
        }
        $template = $this->getTargetItem($batch, 'page_template', $mtPage->getTemplate());
        if (is_object($template)) {
            $data['pTemplateID'] = $template->getPageTemplateID();
        }
        if ($mtPage->getPackage()) {
            $pkg = app(PackageService::class)->getByHandle($mtPage->getPackage());
            if ($pkg) {
                $data['pkgID'] = $pkg->getPackageID();
            }
        }
        return $ccmParentPage->add($type, $data);
    }
    
    private function publishAliasPage(BatchInterface $batch, LoggerInterface $logger, CCMPage $ccmParentPage, MTPage $mtPage): CCMPage
    {
        $slugs = preg_split('{/}', (string) $mtPage->getBatchPath(), -1, PREG_SPLIT_NO_EMPTY);
        $cHandle = array_pop($slugs);
        if ($cHandle === null) {
            throw new UserMessageException(t('Missing the path of the external link'));
        }
        $targetPage = $this->getOrCreatePageByPath($batch, $logger, $mtPage->getCollection(), $mtPage->getTarget());
        $alias = $targetPage->createAlias($ccmParentPage, [
            'name' => (string) $mtPage->getName(),
            'handle' => $cHandle,
            'uID' => $this->getUserID($batch, $mtPage->getUser()),
        ]);

        return $alias;
    }

    private function publishExternalLink(BatchInterface $batch, CCMPage $ccmParentPage, MTPage $mtPage): CCMPage
    {
        $slugs = preg_split('{/}', (string) $mtPage->getBatchPath(), -1, PREG_SPLIT_NO_EMPTY);
        $cHandle = array_pop($slugs);
        if ($cHandle === null) {
            throw new UserMessageException(t('Missing the path of the external link'));
        }
        $ccmPage = $ccmParentPage->addExternalLink(
            (string) $mtPage->getName(),
            (string) $mtPage->getTarget(),
            [
                'newWindow' => $mtPage->isNewWindow(),
                'handle' => $cHandle,
                'uID' => $this->getUserID($batch, $mtPage->getUser()),
            ]
        );

        return $ccmPage;
    }

    private function getUserID(BatchInterface $batch, ?string $userName): int
    {
        $ui = $this->getTargetItem($batch, 'user', $userName);

        return $ui ? (int) $ui->getUserID() : (int) USER_SUPER_ID;
    }

    private function getParentPage(BatchInterface $batch, LoggerInterface $logger, MTPage $childPage): CCMPage
    {
        $slugs = preg_split('{/}', (string) $childPage->getBatchPath(), -1, PREG_SPLIT_NO_EMPTY);
        array_pop($slugs);

        return $this->getOrCreatePageByPath($batch, $logger, $childPage->getCollection(), implode('/', $slugs));
    }

    /**
     * {@inheritdoc}
     *
     * @see \PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler\AbstractPageCommandHandler::getPageByPath()
     */
    private function getOrCreatePageByPath(BatchInterface $batch, LoggerInterface $logger, PageObjectCollection $collection, string $path): CCMPage
    {
        $root = $this->getBatchParentPage($batch);
        $slugs = preg_split('{/}', $path, -1, PREG_SPLIT_NO_EMPTY);
        if ($slugs === []) {
            return $root;
        }
        $site = $batch->getSite();
        $ccmPage = $root;
        $prefix = '';
        $textService = app(Text::class);
        $repo = app(EntityManagerInterface::class)->getRepository(MTPage::class);
        foreach ($slugs as $slug) {
            $currentPath = $prefix . $slug;
            $prefix = $currentPath . '/';
            $c = CCMPage::getByPath($root->getCollectionPath() . '/' . $currentPath, 'RECENT', $site);
            if ($c && $c->getError() !== COLLECTION_NOT_FOUND) {
                $ccmPage = $c;
            } else {
                $newMTPage = $repo->findOneBy([
                    'collection' => $collection,
                    'batch_path' => $currentPath,
                ]);
                if ($newMTPage !== null) {
                    $ccmPage = $this->publishPage($batch, $logger, $newMTPage);
                } else {
                    $ccmPage = $ccmPage->add(null, [
                        'cHandle' => $slug,
                        'name' => $textService->unhandle($slug),
                        'uID' => USER_SUPER_ID,
                    ]);
                }
            }
            $prefix = $currentPath . '/';
        }

        return $ccmPage;
    }

    protected function getBatchParentPage(Batch $batch): CCMPage
    {
        if ($this->batchParentPage === null) {
            $this->batchParentPage = $this->getPageByPath($batch, '');
            if ($this->batchParentPage === null) {
                if ($batch->isPublishToSitemap()) {
                    throw new UserMessageException(t('Unable to find the home page of the website'));
                }
                $ccmParent = CCMPage::getByPath('/!import_batches', 'RECENT', $batch->getSite());
                $type = Type::getByHandle('import_batch');
                $this->batchParentPage = $ccmParent->add(
                    $type,
                    [
                        'cName' => $batch->getID(),
                        'pkgID' => app(PackageService::class)->getByHandle('migration_tool')->getPackageID(),
                    ]
                );
            }
        }

        return $this->batchParentPage;
    }
}
