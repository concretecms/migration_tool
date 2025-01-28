<?php

namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler;

use Concrete\Core\Entity\Page\PagePath;
use Concrete\Core\Error\UserMessageException;
use Concrete\Core\Localization\Locale\Service as LocaleService;
use Concrete\Core\Package\PackageService;
use Concrete\Core\Page\Page as CCMPage;
use Concrete\Core\Page\Type\Type;
use Concrete\Core\Utility\Service\Text;
use Doctrine\ORM\EntityManagerInterface;
use PortlandLabs\Concrete5\MigrationTool\Batch\BatchInterface;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\Batch;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\Page as MTPage;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\PageObjectCollection;
use PortlandLabs\Concrete5\MigrationTool\Publisher\Logger\LoggerInterface;
use Concrete\Core\Multilingual\Service\Detector;

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

    private function publishPage(Batch $batch, LoggerInterface $logger, MTPage $mtPage): CCMPage
    {
        $ccmPage = $this->getPageByPath($batch, $mtPage->getBatchPath());
        if ($ccmPage !== null) {
            $this->updateExistingPage($batch, $mtPage, $ccmPage);
        } else {
            $logger->logPublishStarted($mtPage);
            $ccmParentPage = $this->getParentPage($batch, $logger, $mtPage);
            switch ($mtPage->getKind()) {
                case MTPage::KIND_ALIAS:
                    $ccmPage = $this->createAliasPage($batch, $logger, $ccmParentPage, $mtPage);
                    break;
                case MTPage::KIND_EXTERNAL_LINK:
                    $ccmPage = $this->createExternalLink($batch, $ccmParentPage, $mtPage);
                    break;
                case MTPage::KIND_REGULAR_PAGE:
                default:
                    $ccmPage = $this->createRegularPage($batch, $ccmParentPage, $mtPage);
                    break;
            }
            $this->setAdditionalPaths($mtPage, $ccmPage);
            $logger->logPublishComplete($mtPage, $ccmPage);
        }

        return $ccmPage;
    }

    private function createRegularPage(Batch $batch, ?CCMPage $ccmParentPage, MTPage $mtPage): CCMPage
    {
        if ($ccmParentPage === null) {
            throw new UserMessageException(t('Unable to find the home page of the website'));
        }
        $slugs = preg_split('{/}', (string) $mtPage->getBatchPath(), -1, PREG_SPLIT_NO_EMPTY);
        $pageType = null;
        $pageTemplate = null;
        $data = [
            'uID' => $this->getUserID($batch, $mtPage->getUser()),
            'name' => $mtPage->getName() ?: null,
            'cDescription' => $mtPage->getDescription(),
            'cHandle' => array_pop($slugs) ?? '',
            'cDatePublic' => $mtPage->getPublicDate() ?: null,
            'ptID' => $this->getPageTypeID($batch, $mtPage->getType(), $pageType),
            'pTemplateID' => $this->getPageTemplateID($batch, $mtPage->getTemplate(), $pageTemplate),
            'pkgID' => $this->getPackageID($batch, $mtPage->getPackage()),
        ];
        $localeInfo = $mtPage->getLocaleRoot();
        if ($localeInfo === null || $batch->isPublishToSitemap() !== true || $this->localeAlreadyExists($batch, $localeInfo)) {
            return $ccmParentPage->add($pageType, $data);
        }
        if (!$pageTemplate) {
            throw new UserMessageException(t('Missing page template when creating the home of a language'));
        }
        $app = app();
        $detector = $app->make('multilingual/detector');
        if (method_exists($detector, 'assumeEnabled')) {
            $detector->assumeEnabled();
        } else {
            $app->forgetInstance('multilingual/detector');
            $app->forgetInstance(Detector::class);
        }
        $service = $app->make(LocaleService::class);
        $locale = $service->add($batch->getSite(), $localeInfo['language'], $localeInfo['country']);
        $page = $service->addHomePage($locale, $pageTemplate, $data['name'] ?? 'Home', $data['cHandle']);
        $page->update([
            'cDescription' => $data['cDescription'],
            'cDatePublic' => $data['cDatePublic'],
            'ptID' => $data['ptID'],
            'uID' => $data['uID'],
            'pkgID' => $data['pkgID'],
        ]);

        return $page;
    }

    private function setAdditionalPaths(MTPage $mtPage, CCMPage $ccmPage): void
    {
        $em = app(EntityManagerInterface::class);
        foreach ($ccmPage->getAdditionalPagePaths() as $ccmPagePath) {
            $em->remove($ccmPagePath);
        }
        foreach ($mtPage->getAdditionalPaths() as $mtAdditionalPath) {
            $ccmPagePath = new PagePath();
            $ccmPagePath->setPageObject($ccmPage);
            $ccmPagePath->setPagePath('/' . trim($mtAdditionalPath->getPath(), '/'));
            $em->persist($ccmPagePath);
        }
        $em->flush();
    }

    private function createAliasPage(Batch $batch, LoggerInterface $logger, ?CCMPage $ccmParentPage, MTPage $mtPage): CCMPage
    {
        if ($ccmParentPage === null) {
            throw new UserMessageException(t("The website home page can't be an alias"));
        }
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

    private function createExternalLink(Batch $batch, ?CCMPage $ccmParentPage, MTPage $mtPage): CCMPage
    {
        if ($ccmParentPage === null) {
            throw new UserMessageException(t("The website home page can't be an external link"));
        }
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

    private function getUserID(Batch $batch, ?string $userName): int
    {
        $ui = $this->getTargetItem($batch, 'user', $userName);

        return $ui ? (int) $ui->getUserID() : (int) USER_SUPER_ID;
    }

    private function getPageTemplateID(Batch $batch, ?string $handle, &$pageTemplate = null): ?int
    {
        $pageTemplate = $this->getTargetItem($batch, 'page_template', $handle) ?: null;
        if (!$pageTemplate) {
            return null;
        }

        return (int) $pageTemplate->getPageTemplateID() ?: null;
    }

    private function getPackageID(Batch $batch, ?string $handle): ?int
    {
        if ($handle === null || $handle === '') {
            return null;
        }
        $pkg = app(PackageService::class)->getByHandle($handle);
        if (!$pkg) {
            return null;
        }

        return (int) $pkg->getPackageID() ?: null;
    }

    private function getPageTypeID(Batch $batch, ?string $handle, &$pageType = null): ?int
    {
        $pageType = $this->getTargetItem($batch, 'page_type', $handle) ?: null;
        if (!$pageType) {
            return null;
        }

        return (int) $pageType->getPageTypeID() ?: null;
    }

    /**
     * @return \Concrete\Core\Page\Page|null returns NULL if and only if $mtPage is the website actual root page
     */
    private function getParentPage(Batch $batch, LoggerInterface $logger, MTPage $mtPage): ?CCMPage
    {
        $slugs = preg_split('{/}', (string) $mtPage->getBatchPath(), -1, PREG_SPLIT_NO_EMPTY);
        if ($slugs === []) {
            return null;
        }
        array_pop($slugs);

        return $this->getOrCreatePageByPath($batch, $logger, $mtPage->getCollection(), implode('/', $slugs));
    }

    /**
     * {@inheritdoc}
     *
     * @see \PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler\AbstractPageCommandHandler::getPageByPath()
     */
    private function getOrCreatePageByPath(Batch $batch, LoggerInterface $logger, PageObjectCollection $collection, string $path): CCMPage
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
                        'pkgID' => $this->getPackageID($batch, 'migration_tool'),
                    ]
                );
            }
        }

        return $this->batchParentPage;
    }

    private function updateExistingPage(Batch $batch, MTPage $mtPage, CCMPage $ccmPage): void
    {
        $this->setAdditionalPaths($mtPage, $ccmPage);
        switch ($mtPage->getKind()) {
            case MTPage::KIND_ALIAS:
                break;
            case MTPage::KIND_EXTERNAL_LINK:
                break;
            case MTPage::KIND_REGULAR_PAGE:
            default:
                $ccmPage->update([
                    'cName' => $mtPage->getName() ?: null,
                    'cDescription' => $mtPage->getDescription(),
                    'cDatePublic' => $mtPage->getPublicDate() ?: null,
                    'ptID' =>  $this->getPageTypeID($batch, $mtPage->getType()),
                    'pTemplateID' => $this->getPageTemplateID($batch, $mtPage->getTemplate()),
                    'uID' => $this->getUserID($batch, $mtPage->getUser()),
                    'pkgID' => $this->getPackageID($batch, $mtPage->getPackage()),
                ]);
                $this->updateExistingLocale($batch, $ccmPage, $mtPage->getLocaleRoot());
                break;
        }
    }

    private function updateExistingLocale(Batch $batch, CCMPage $ccmPage, ?array $localeInfo): void
    {
        if ($localeInfo === null || $batch->isPublishToSitemap() !== true) {
            return;
        }
        $pageTree = $ccmPage->getSiteTreeObject();
        if (!$pageTree || $pageTree->getSiteHomePageID() != $ccmPage->getCollectionID()) {
            return;
        }
        $editingLocale = $pageTree->getLocale();
        if ($editingLocale->getLanguage() === $localeInfo['language'] && $editingLocale->getCountry() === $localeInfo['country']) {
            return;
        }
        if ($this->localeAlreadyExists($batch, $localeInfo)) {
            return;
        }
        $editingLocale->setLanguage($localeInfo['language']);
        $editingLocale->setCountry($localeInfo['country']);
        $service = app(LocaleService::class);
        $service->updatePluralSettings($editingLocale);
        $em = app(EntityManagerInterface::class);
        $em->flush();
    }

    private function localeAlreadyExists(Batch $batch, array $localeInfo): bool
    {
        foreach ($batch->getSite()->getLocales() as $locale) {
            if ($locale->getLanguage() === $localeInfo['language'] && $locale->getCountry() === $localeInfo['country']) {
                return true;
            }
        }

        return false;
    }
}
