<?php

declare(strict_types=1);

namespace PortlandLabs\Concrete5\MigrationTool\Batch\Validator\Pipeline\Stage;

use League\Pipeline\StageInterface;

defined('C5_EXECUTE') or die("Access Denied.");

class ValidateExternalLinkStage implements StageInterface
{
    /**
     * @param \PortlandLabs\Concrete5\MigrationTool\Batch\Validator\ValidatorResult $result
     *
     * @return \PortlandLabs\Concrete5\MigrationTool\Batch\Validator\ValidatorResult
     */
    public function __invoke($result)
    {
        $subject = $result->getSubject();
        /** @var \PortlandLabs\Concrete5\MigrationTool\Batch\Validator\BatchObjectValidatorSubject $subject */
        $page = $subject->getObject();
        /** @var \PortlandLabs\Concrete5\MigrationTool\Entity\Import\Page $page */
        if ($page->getKind() !== $page::KIND_EXTERNAL_LINK) {
            return $result;
        }
        // @todo
        
        return $result;
    }
}
