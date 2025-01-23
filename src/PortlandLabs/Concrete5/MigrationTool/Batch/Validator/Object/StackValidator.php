<?php
namespace PortlandLabs\Concrete5\MigrationTool\Batch\Validator\Object;

use PortlandLabs\Concrete5\MigrationTool\Batch\Validator\BatchObjectValidatorSubject;
use PortlandLabs\Concrete5\MigrationTool\Batch\Validator\MessageCollection;
use PortlandLabs\Concrete5\MigrationTool\Batch\Validator\ValidatorInterface;
use PortlandLabs\Concrete5\MigrationTool\Batch\Validator\ValidatorResult;
use PortlandLabs\Concrete5\MigrationTool\Batch\Validator\ValidatorSubjectInterface;

class StackValidator implements ValidatorInterface
{
    public function validate(ValidatorSubjectInterface $subject)
    {
        /**
         * @var $subject BatchObjectValidatorSubject
         */
        $result = new ValidatorResult($subject);
        $stack = $subject->getObject();
        $blocks = $stack->getBlocks();
        $validator = \Core::make('migration/batch/block/validator');
        foreach($blocks as $block) {
            $blockSubject = new BatchObjectValidatorSubject($subject->getBatch(), $block);
            $blockResult = $validator->validate($blockSubject);
            $result->getMessages()->addMessages($blockResult->getMessages());
        }
        return $result;
    }
}
