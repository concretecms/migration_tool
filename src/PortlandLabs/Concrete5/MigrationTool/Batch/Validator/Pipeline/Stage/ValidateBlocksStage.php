<?php
namespace PortlandLabs\Concrete5\MigrationTool\Batch\Validator\Pipeline\Stage;

use League\Pipeline\StageInterface;
use PortlandLabs\Concrete5\MigrationTool\Batch\Validator\BatchObjectValidatorSubject;

defined('C5_EXECUTE') or die("Access Denied.");

class ValidateBlocksStage implements StageInterface
{
    public function __invoke($result)
    {
        $subject = $result->getSubject();
        $batch = $subject->getBatch();
        $page = $subject->getObject();
        /** @var \PortlandLabs\Concrete5\MigrationTool\Entity\Import\Page $page */
        if (in_array($page->getKind(), [$page::KIND_ALIAS, $page::KIND_EXTERNAL_LINK], true)) {
            return $result;
        }
        $areas = $page->getAreas();
        foreach ($areas as $area) {
            $blocks = $area->getBlocks();
            $validator = \Core::make('migration/batch/block/validator');
            foreach($blocks as $block) {
                $validatorSubject = new BatchObjectValidatorSubject($batch, $block);
                $validatorResult = $validator->validate($validatorSubject);
                foreach ($validatorResult->getMessages() as $message) {
                    $result->getMessages()->add($message);
                }
            }
        }

        return $result;
    }

}
