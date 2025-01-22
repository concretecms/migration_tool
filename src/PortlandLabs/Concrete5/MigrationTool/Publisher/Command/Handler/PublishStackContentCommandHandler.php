<?php
namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler;

use Concrete\Core\Page\Stack\Stack;
use PortlandLabs\Concrete5\MigrationTool\Batch\BatchInterface;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\AbstractStack;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\Stack as ImportStack;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\GlobalArea as ImportGlobalArea;
use PortlandLabs\Concrete5\MigrationTool\Publisher\Logger\LoggerInterface;
use PortlandLabs\Concrete5\MigrationTool\Publisher\Service\StackTrait;
use Doctrine\ORM\EntityManagerInterface;
use Concrete\Core\Multilingual\Page\Section\Section;

defined('C5_EXECUTE') or die("Access Denied.");

/**
 * @property \PortlandLabs\Concrete5\MigrationTool\Publisher\Command\PublishStackContentCommand $command
 */
class PublishStackContentCommandHandler extends AbstractPageCommandHandler
{
    use StackTrait;

    /**
     * {@inheritdoc}
     *
     * @see \PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler\AbstractPageCommandHandler::getPage()
     *
     * @return \PortlandLabs\Concrete5\MigrationTool\Entity\Import\AbstractStack|null
     */
    public function getPage($id)
    {
        $em = app(EntityManagerInterface::class);

        return $em->find(AbstractStack::class, $id);
    }

    /**
     * {@inheritdoc}
     *
     * @see \PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler\AbstractHandler::execute()
     */
    public function execute(BatchInterface $batch, LoggerInterface $logger)
    {
        $importStack = $this->getPage($this->command->getPageId());
        $name = (string) $importStack->getName();
        if ($name === '') {
            return;
        }
        $site = $batch->getSite();
        if ($importStack instanceof ImportGlobalArea) {
            $stack = Stack::getByName($name, 'RECENT', $site);
        } elseif ($importStack instanceof ImportStack) {
            $folder = $this->getOrCreateFolderByPath((string) $importStack->getPath());
            $stackID = $this->getStackIDByName($name, $folder);
            $stack = $stackID ? Stack::getByID($stackID) : null;
        } else {
            $stack = null;
        }
        if (!$stack || $stack->isError()) {
            return;
        }
        $localeID = $importStack->getLocaleID();
        if ($localeID) {
            $section = Section::getByLocale($localeID, $site);
            if (!$section) {
                return;
            }
            $localizedStack = $stack->getLocalizedStack($section);
            $stack = $localizedStack ?: $stack->addLocalizedStack($section, ['copyContents' => false]);
        }
        foreach ($importStack->getBlocks() as $importBlock) {
            /** @var \\PortlandLabs\Concrete5\MigrationTool\Entity\Import\StackBlock $importBlock */
            $blockType = $this->getTargetItem($batch, 'block_type', $importBlock->getType());
            if (!is_object($blockType)) {
                continue;
            }
            $value = $importBlock->getBlockValue();
            $publisher = $value->getPublisher();
            $publisher->publish($batch, $blockType, $stack, STACKS_AREA_NAME, $value);
        }
    }
}
