<?php
namespace PortlandLabs\Concrete5\MigrationTool\Batch\Validator\Pipeline\Stage;

use League\Pipeline\StageInterface;
use PortlandLabs\Concrete5\MigrationTool\Batch\Validator\BatchObjectCollectionValidatorSubject;
use PortlandLabs\Concrete5\MigrationTool\Batch\Validator\BatchObjectValidatorSubject;

class ValidateBlockValuesStage implements StageInterface
{

    public function __invoke($result)
    {
        $subject = $result->getSubject();
        $batch = $subject->getBatch();
        $block = $subject->getObject();

        $value = $block->getBlockValue();
        if ($value) {
            $validator = $value->getRecordValidator($batch);
            if (is_object($validator)) {
                $valueSubject = new BatchObjectValidatorSubject($batch, $value);
                $validatorResult = $validator->validate($valueSubject);
                foreach ($validatorResult->getMessages() as $message) {
                    $result->getMessages()->add($message);
                }
            }
        }

        return $result;
    }

}
