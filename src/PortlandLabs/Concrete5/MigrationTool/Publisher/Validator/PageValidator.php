<?php
namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Validator;

use PortlandLabs\Concrete5\MigrationTool\Batch\ContentMapper\Item\Item;
use PortlandLabs\Concrete5\MigrationTool\Entity\ContentMapper\IgnoredTargetItem;
use Concrete\Core\Page\Page as CCMPage;

/**
 * @property \PortlandLabs\Concrete5\MigrationTool\Entity\Import\Page $object
 */
class PageValidator extends AbstractValidator
{
    /**
     * @var bool|null
     */
    private $skip = null;

    /**
     * {@inheritdoc}
     *
     * @see \PortlandLabs\Concrete5\MigrationTool\Publisher\Validator\ValidatorInterface::skipItem()
     */
    public function skipItem()
    {
        if ($this->skip === null) {
            $this->skip = $this->calculateSkip();
        }

        return $this->skip;
    }

    private function calculateSkip(): bool
    {
        $batch = $this->getBatch($this->object);
        
        // This code checks to see if the page type for the current page is being ignored globally.
        // If it is, then we ignore this page.
        $mappers = app('migration/manager/mapping');
        $mapper = $mappers->driver('page_type');
        $list = $mappers->createTargetItemList($batch, $mapper);
        $item = new Item($this->object->getType());
        $targetItem = $list->getSelectedTargetItem($item);
        if ($targetItem instanceof IgnoredTargetItem) {
            return true;
        }
        if ($batch->isPublishToSitemap()) {
            $path = trim($this->object->getBatchPath() ?? '', '/');
            if ($path === '') {
                $ccmPage = $batch->getSite()->getSiteHomePageObject();
            } else {
                $ccmPage = CCMPage::getByPath('/' . $path, 'RECENT', $batch->getSite());
            }
            if ($ccmPage && !$ccmPage->isError()) {
                if (!empty($ccmPage->getBlockIDs())) {
                    return true;
                }
            }
        }

        return false;
    }
}
