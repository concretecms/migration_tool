<?php
namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler;

use Concrete\Core\Attribute\Key\CollectionKey;
use Concrete\Core\Page\Template;
use PortlandLabs\Concrete5\MigrationTool\Batch\BatchInterface;
use PortlandLabs\Concrete5\MigrationTool\Batch\ContentMapper\TargetItemList;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\PageType\PageType;
use PortlandLabs\Concrete5\MigrationTool\Publisher\Logger\LoggerInterface;

defined('C5_EXECUTE') or die("Access Denied.");

class CreatePageTypesCommandHandler extends AbstractHandler
{

    public function execute(BatchInterface $batch, LoggerInterface $logger)
    {
        $types = $batch->getObjectCollection('page_type');
        /*
         * @var \PortlandLabs\Concrete5\MigrationTool\Entity\Import\PageType\PageType
         */

        if (!$types) {
            return;
        }

        foreach ($types->getTypes() as $type) {
            /**
             * @var $type PageType
             */
            if (!$type->getPublisherValidator()->skipItem()) {
                $logger->logPublishStarted($type);
                $pkg = null;
                if ($type->getPackage()) {
                    $pkg = \Package::getByHandle($type->getPackage());
                }
                $defaultTemplate = Template::getByHandle($type->getDefaultTemplate());
                $templates = array();
                if ($type->getAllowedTemplates() == 'C' || $type->getAllowedTemplates() == 'X') {
                    foreach ($type->getTemplates() as $templateHandle) {
                        $templates[] = Template::getByHandle($templateHandle);
                    }
                }
                $data = array(
                    'handle' => $type->getHandle(),
                    'name' => $type->getName(),
                    'defaultTemplate' => $defaultTemplate,
                    'allowedTemplates' => $type->getAllowedTemplates(),
                    'internal' => $type->getIsInternal(),
                    'ptLaunchInComposer' => $type->getLaunchInComposer(),
                    'ptIsFrequentlyAdded' => $type->getIsFrequentlyAdded(),
                    'templates' => $templates,
                );

                $pageType = \Concrete\Core\Page\Type\Type::add($data, $pkg);

                foreach ($type->getLayoutSets() as $set) {
                    $layoutSet = $pageType->addPageTypeComposerFormLayoutSet($set->getName(),
                        $set->getDescription()
                    );

                    /*
                     * @var \PortlandLabs\Concrete5\MigrationTool\Entity\Import\PageType\ComposerFormLayoutSetControl
                     */
                    foreach ($set->getControls() as $controlEntity) {
                        $controlType = \Concrete\Core\Page\Type\Composer\Control\Type\Type::getByHandle($controlEntity->getHandle());
                        $control = $controlType->configureFromImportHandle($controlEntity->getItemIdentifier());
                        $setControl = $control->addToPageTypeComposerFormLayoutSet($layoutSet, true);
                        $setControl->updateFormLayoutSetControlRequired($controlEntity->getIsRequired());
                        $setControl->updateFormLayoutSetControlCustomTemplate($controlEntity->getCustomTemplate());
                        $setControl->updateFormLayoutSetControlCustomLabel($controlEntity->getCustomLabel());
                        $setControl->updateFormLayoutSetControlDescription($controlEntity->getDescription());
                    }
                }

                $defaultPages = $type->getDefaultPageCollection();
                foreach ($defaultPages->getPages() as $page) {
                    $pageTemplate = Template::getByHandle($page->getTemplate());

                    $concretePage = $pageType->getPageTypePageTemplateDefaultPageObject($pageTemplate);

                    // if the $handle matches the default page template for this page type, then we ALSO check in here
                    // and see if there are any attributes
                    if (is_object($defaultTemplate) && $defaultTemplate->getPageTemplateHandle() == $pageTemplate->getPageTemplateHandle()) {
                        if ($page->attributes) {
                            foreach ($page->attributes as $attribute) {
                                $ak = TargetItemList::getBatchTargetItem(
                                    $batch,
                                    'page_attribute',
                                    $attribute->getAttribute()->getHandle()
                                );
                                if (is_object($ak)) {
                                    $logger->logPublishStarted($attribute);
                                    $value = $attribute->getAttribute()->getAttributeValue();
                                    $publisher = $value->getPublisher();
                                    $publisher->publish($batch, $ak, $concretePage, $value);
                                    $logger->logPublishComplete($attribute);
                                }
                            }
                        }
                    }

                    if ($page->areas) {
                        foreach ($page->areas as $area) {
                            $areaName = TargetItemList::getBatchTargetItem($batch, 'area', $area->getName());
                            $styleSet = $area->getStyleSet();
                            if ($areaName) {
                                if (is_object($styleSet)) {
                                    $styleSetPublisher = $styleSet->getPublisher();
                                    $publishedStyleSet = $styleSetPublisher->publish();
                                    $concreteArea = \Area::getOrCreate($concretePage, $areaName);
                                    $concretePage->setCustomStyleSet($concreteArea, $publishedStyleSet);
                                }
                                if ($area->blocks) {
                                    foreach ($area->blocks as $block) {
                                        $bt = TargetItemList::getBatchTargetItem(
                                            $batch,
                                            'block_type',
                                            $block->getType()
                                        );
                                        if (is_object($bt)) {
                                            $logger->logPublishStarted($block);
                                            $value = $block->getBlockValue();
                                            $publisher = $value->getPublisher();
                                            $b = $publisher->publish($batch, $bt, $concretePage, $areaName, $value);
                                            $logger->logPublishComplete($block, $b);
                                            $styleSet = $block->getStyleSet();
                                            if (is_object($styleSet)) {
                                                $styleSetPublisher = $styleSet->getPublisher();
                                                $publishedStyleSet = $styleSetPublisher->publish();
                                                $b->setCustomStyleSet($publishedStyleSet);
                                            }
                                            if ($block->getCustomTemplate()) {
                                                $b->setCustomTemplate($block->getCustomTemplate());
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }


                $logger->logPublishComplete($type, $pageType);
            } else {
                $logger->logSkipped($type);
            }
        }
    }
}
