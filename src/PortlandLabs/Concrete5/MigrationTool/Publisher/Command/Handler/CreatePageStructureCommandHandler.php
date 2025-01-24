<?php

namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler;

use Concrete\Core\Page\Page;
use Concrete\Core\Utility\Service\Text;
use Doctrine\ORM\EntityManagerInterface;
use PortlandLabs\Concrete5\MigrationTool\Batch\BatchInterface;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\Page as ImportPage;
use PortlandLabs\Concrete5\MigrationTool\Publisher\Logger\LoggerInterface;
use Concrete\Core\Package\PackageService;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\PageObjectCollection;
use Concrete\Core\Error\UserMessageException;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @property \PortlandLabs\Concrete5\MigrationTool\Publisher\Command\CreatePageStructureCommand $command
 */
class CreatePageStructureCommandHandler extends AbstractPageCommandHandler
{
    public function execute(BatchInterface $batch, LoggerInterface $logger)
    {
        $importPage = $this->getPage($this->command->getPageId());
        $this->publishPage($batch, $logger, $importPage);
    }
    /**
     * @throws \PortlandLabs\Concrete5\MigrationTool\Publisher\Service\MissingPageAtPathException
     */
    protected function publishPage(BatchInterface $batch, LoggerInterface $logger, ImportPage $importPage): Page
    {
        $already = $this->getPageByPath($batch, $importPage->getBatchPath());
        if ($already && !$already->isError()) {
            return $already;
        }
        $logger->logPublishStarted($importPage);
        $parentPage = $this->getParentPage($batch, $logger, $importPage);
        switch ($importPage->getKind()) {
            case ImportPage::KIND_ALIAS:
                $publishedPage = $this->publishAliasPage($batch, $logger, $parentPage, $importPage);
                break;
            case ImportPage::KIND_EXTERNAL_LINK:
                $publishedPage = $this->publishExternalLink($batch, $parentPage, $importPage);
                break;
            case ImportPage::KIND_REGULAR_PAGE:
            default:
                $publishedPage = $this->publishRegularPage($batch, $parentPage, $importPage);
                break;
        }
        $logger->logPublishComplete($importPage, $publishedPage);

        return $publishedPage;
    }

    protected function publishRegularPage(BatchInterface $batch, Page $parentPage, ImportPage $importPage): Page
    {
        $slugs = preg_split('{/}', (string) $importPage->getBatchPath(), -1, PREG_SPLIT_NO_EMPTY);
        $data = [
            'uID' => $this->getUserID($batch, $importPage->getUser()),
            'name' => $importPage->getName(),
            'cDescription' => $importPage->getDescription(),
            'cHandle' => array_pop($slugs) ?? '',
        ];
        $cDatePublic = $importPage->getPublicDate();
        if ($cDatePublic) {
            $data['cDatePublic'] = $cDatePublic;
        }
        $type = $this->getTargetItem($batch, 'page_type', $importPage->getType());
        if ($type) {
            $data['ptID'] = $type->getPageTypeID();
        }
        $template = $this->getTargetItem($batch, 'page_template', $importPage->getTemplate());
        if (is_object($template)) {
            $data['pTemplateID'] = $template->getPageTemplateID();
        }
        if ($importPage->getPackage()) {
            $pkg = app(PackageService::class)->getByHandle($importPage->getPackage());
            if ($pkg) {
                $data['pkgID'] = $pkg->getPackageID();
            }
        }
        return $parentPage->add($type, $data);
    }
    
    protected function publishAliasPage(BatchInterface $batch, LoggerInterface $logger, Page $parentPage, ImportPage $importPage): Page
    {
        $slugs = preg_split('{/}', (string) $importPage->getBatchPath(), -1, PREG_SPLIT_NO_EMPTY);
        $cHandle = array_pop($slugs);
        if ($cHandle === null) {
            throw new UserMessageException(t('Missing the path of the external link'));
        }
        $targetPage = $this->getOrCreatePageByPath($batch, $logger, $importPage->getCollection(), $importPage->getTarget());
        $alias = $targetPage->createAlias($parentPage, [
            'name' => (string) $importPage->getName(),
            'handle' => $cHandle,
            'uID' => $this->getUserID($batch, $importPage->getUser()),
        ]);

        return $alias;
    }

    protected function publishExternalLink(BatchInterface $batch, Page $parentPage, ImportPage $importPage): Page
    {
        $slugs = preg_split('{/}', (string) $importPage->getBatchPath(), -1, PREG_SPLIT_NO_EMPTY);
        $cHandle = array_pop($slugs);
        if ($cHandle === null) {
            throw new UserMessageException(t('Missing the path of the external link'));
        }
        $page = $parentPage->addExternalLink(
            (string) $importPage->getName(),
            (string) $importPage->getTarget(),
            [
                'newWindow' => $importPage->isNewWindow(),
                'handle' => $cHandle,
                'uID' => $this->getUserID($batch, $importPage->getUser()),
            ]
        );

        return $page;
    }

    private function getUserID(BatchInterface $batch, ?string $userName): int
    {
        $ui = $this->getTargetItem($batch, 'user', $userName);

        return $ui ? (int) $ui->getUserID() : (int) USER_SUPER_ID;
    }

    private function getParentPage(BatchInterface $batch, LoggerInterface $logger, ImportPage $childPage): Page
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
    protected function getOrCreatePageByPath(BatchInterface $batch, LoggerInterface $logger, PageObjectCollection $collection, string $path): Page
    {
        $root = $this->getBatchParentPage($batch);
        $slugs = preg_split('{/}', $path, -1, PREG_SPLIT_NO_EMPTY);
        if ($slugs === []) {
            return $root;
        }
        $site = $batch->getSite();
        $page = $root;
        $prefix = '';
        $textService = app(Text::class);
        $repo = app(EntityManagerInterface::class)->getRepository(ImportPage::class);
        foreach ($slugs as $slug) {
            $currentPath = $prefix . $slug;
            $prefix = $currentPath . '/';
            $c = Page::getByPath($root->getCollectionPath() . '/' . $currentPath, 'RECENT', $site);
            if ($c && $c->getError() !== COLLECTION_NOT_FOUND) {
                $page = $c;
            } else {
                $newImportPage = $repo->findOneBy([
                    'collection' => $collection,
                    'batch_path' => $currentPath,
                ]);
                if ($newImportPage !== null) {
                    $page = $this->publishPage($batch, $logger, $newImportPage);
                } else {
                    $page = $page->add(null, [
                        'cHandle' => $slug,
                        'name' => $textService->unhandle($slug),
                        'uID' => USER_SUPER_ID,
                    ]);
                }
            }
            $prefix = $currentPath . '/';
        }

        return $page;
    }
}
