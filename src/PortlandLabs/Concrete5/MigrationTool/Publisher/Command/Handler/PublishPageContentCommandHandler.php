<?php
namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler;

use Concrete\Core\Multilingual\Page\Section\Section;
use Concrete\Core\Page\Page as CCMPage;
use Concrete\Core\Page\Type\Composer\FormLayoutSetControl;
use Doctrine\ORM\EntityManagerInterface;
use PortlandLabs\Concrete5\MigrationTool\Batch\BatchInterface;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\Batch;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\Page as MTPage;
use PortlandLabs\Concrete5\MigrationTool\Publisher\Logger\LoggerInterface;

defined('C5_EXECUTE') or die("Access Denied.");

/**
 * @property \PortlandLabs\Concrete5\MigrationTool\Publisher\Command\PublishPageContentCommand $command
 */
class PublishPageContentCommandHandler extends AbstractPageCommandHandler
{
    public function execute(BatchInterface $batch, LoggerInterface $logger)
    {
        $mtPage = $this->getPage($this->command->getPageId());
        switch ($mtPage->getKind()) {
            case MTPage::KIND_ALIAS:
            case MTPage::KIND_EXTERNAL_LINK:
                return;
        }
        $ccmPage = $this->getPageByPath($batch, $mtPage->getBatchPath());
        $this->publishHRefLangs($batch, $mtPage, $ccmPage);
        foreach ($mtPage->getAttributes() as $mtAttribute) {
            $ak = $this->getTargetItem($batch, 'page_attribute', $mtAttribute->getAttribute()->getHandle());
            if (is_object($ak)) {
                $logger->logPublishStarted($mtAttribute);
                $value = $mtAttribute->getAttribute()->getAttributeValue();
                $publisher = $value->getPublisher();
                $publisher->publish($batch, $ak, $ccmPage, $value);
                $logger->logPublishComplete($mtAttribute);
            }
        }
        $em = app(EntityManagerInterface::class);
        $repo = $em->getRepository(\PortlandLabs\Concrete5\MigrationTool\Entity\ContentMapper\TargetItem::class);
        $controls = $repo->findBy(['item_type' => 'composer_output_content']);
        $controlHandles = array_map(static function ($a) { return $a->getItemID(); }, $controls);
        $blockSubstitutes = [];
        // Now we have our $controls array which we will use to determine if any of the blocks on this page
        // need to be replaced by another block.

        foreach ($mtPage->getAreas() as $mtArea) {
            $areaName = (string) $this->getTargetItem($batch, 'area', $mtArea->getName());
            if ($areaName === '') {
                continue;
            }
            $mtStyleSet = $mtArea->getStyleSet();
            if (is_object($mtStyleSet)) {
                $styleSetPublisher = $mtStyleSet->getPublisher();
                $publishedStyleSet = $styleSetPublisher->publish();
                $ccmArea = \Concrete\Core\Area\Area::getOrCreate($ccmPage, $areaName);
                $ccmPage->setCustomStyleSet($ccmArea, $publishedStyleSet);
            }
            foreach ($mtArea->getBlocks() as $mtBlock) {
                $blockType = $this->getTargetItem($batch, 'block_type', $mtBlock->getType());
                if (!is_object($blockType)) {
                    continue;
                }
                $logger->logPublishStarted($mtBlock);
                $value = $mtBlock->getBlockValue();
                $publisher = $value->getPublisher();
                $ccmBlock = $publisher->publish($batch, $blockType, $ccmPage, $areaName, $value);
                if (!is_object($ccmBlock)) {
                    $ccmBlock = null;
                }
                if ($ccmBlock !== null) {
                    $styleSet = $mtBlock->getStyleSet();
                    if (is_object($styleSet)) {
                        $styleSetPublisher = $styleSet->getPublisher();
                        $publishedStyleSet = $styleSetPublisher->publish();
                        $ccmBlock->setCustomStyleSet($publishedStyleSet);
                    }
                    $customTemplate = (string) $mtBlock->getCustomTemplate();
                    if ($customTemplate !== '') {
                        $ccmBlock->setCustomTemplate($customTemplate);
                    }
                    if (in_array($blockType->getBlockTypeHandle(), $controlHandles)) {
                        $blockSubstitutes[$blockType->getBlockTypeHandle()] = $ccmBlock;
                    }
                }
                $logger->logPublishComplete($mtBlock, $ccmBlock);
            }
        }

        // Loop through all the blocks on the page. If any of them are composer output content blocks
        // We look in our composer mapping.
        foreach ($ccmPage->getBlocks() as $ccmBlock) {
            if ($ccmBlock->getBlockTypeHandle() !== BLOCK_HANDLE_PAGE_TYPE_OUTPUT_PROXY) {
                continue;
            }
            foreach ($controls as $targetItem) {
                if (!$targetItem->isMapped()) {
                    continue;
                }
                if ((int) $targetItem->getSourceItemIdentifier() != (int) $ccmBlock->getBlockID()) {
                    continue;
                }
                $substitute = $blockSubstitutes[$targetItem->getItemID()] ?? null;
                if (!$substitute) {
                    continue;
                }
                // We move the substitute to where the proxy block was.
                $blockDisplayOrder = $ccmBlock->getBlockDisplayOrder();
                $substitute->setAbsoluteBlockDisplayOrder($blockDisplayOrder);
                $control = $ccmBlock->getController()->getComposerOutputControlObject();
                if (!is_object($control)) {
                    continue;
                }
                $control = FormLayoutSetControl::getByID($control->getPageTypeComposerFormLayoutSetControlID());
                if (!is_object($control)) {
                    continue;
                }
                $blockControl = $control->getPageTypeComposerControlObject();
                if (!is_object($blockControl)) {
                    continue;
                }
                $blockControl->recordPageTypeComposerOutputBlock($substitute);
            }
            // we delete the proxy block
            $ccmBlock->deleteBlock();
        }
    }

    private function publishHRefLangs(Batch $batch, MTPage $mtPage, CCMPage $ccmPage): void
    {
        if ($batch->isPublishToSitemap() !== true) {
            return;
        }
        foreach ($mtPage->getHRefLangs() as $hrefLang) {
            $destinationPage = $this->getPageByPath($batch, $hrefLang->getPathForLocale());
            if ($destinationPage === null || $destinationPage->getCollectionID() == $ccmPage->getCollectionID()) {
                continue;
            }
            $destinationSection = Section::getByID($destinationPage->getCollectionID());
            if (!$destinationSection || $destinationSection->isError()) {
                $destinationSection = Section::getBySectionOfSite($destinationPage);
                if (!$destinationSection || $destinationSection->isError()) {
                    continue;
                }
            }
            if ($destinationSection->getLocale() !== $hrefLang->getLocaleID()) {
                continue;
            }
            if (!Section::isAssigned($ccmPage)) {
                Section::registerPage($ccmPage);
            }
            Section::relatePage($ccmPage, $destinationPage, $destinationSection->getLocale());
        }
    }
}
