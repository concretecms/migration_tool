<?php
namespace PortlandLabs\Concrete5\MigrationTool\Batch\Validator\Pipeline\Stage;

use League\Pipeline\StageInterface;
use PortlandLabs\Concrete5\MigrationTool\Batch\ContentMapper\Item\Item;
use PortlandLabs\Concrete5\MigrationTool\Batch\ContentMapper\TargetItemList;
use PortlandLabs\Concrete5\MigrationTool\Batch\ContentMapper\Type\PageTemplate;
use PortlandLabs\Concrete5\MigrationTool\Batch\Validator\Message;
use PortlandLabs\Concrete5\MigrationTool\Entity\ContentMapper\UnmappedTargetItem;

defined('C5_EXECUTE') or die("Access Denied.");

class ValidatePageTemplatesStage implements StageInterface
{
    /**
     * @param \PortlandLabs\Concrete5\MigrationTool\Batch\Validator\ValidatorResult $result
     *
     * @return \PortlandLabs\Concrete5\MigrationTool\Batch\Validator\ValidatorResult
     */
    public function __invoke($result)
    {
        $subject = $result->getSubject();
        $batch = $subject->getBatch();
        $page = $subject->getObject();
        /** @var \PortlandLabs\Concrete5\MigrationTool\Entity\Import\Page $page */
        if (in_array($page->getKind(), [$page::KIND_ALIAS, $page::KIND_EXTERNAL_LINK], true)) {
            return $result;
        }
        if (!$page->getTemplate()) {
            return $result;
        }
        $mapper = new PageTemplate();
        $targetItemList = new TargetItemList($batch, $mapper);
        $item = new Item($page->getTemplate());
        $targetItem = $targetItemList->getSelectedTargetItem($item);
        if ($targetItem instanceof UnmappedTargetItem) {
            $result->getMessages()->add(
                new Message(t('Page template <strong>%s</strong> does not exist.', $item->getIdentifier()), Message::E_WARNING)
            );
        }

        return $result;
    }

}
