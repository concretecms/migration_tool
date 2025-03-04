<?php
namespace PortlandLabs\Concrete5\MigrationTool\Batch\Formatter;

use PortlandLabs\Concrete5\MigrationTool\Batch\Validator\BatchObjectValidatorSubject;
use PortlandLabs\Concrete5\MigrationTool\Batch\Validator\MessageCollection;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\ObjectCollection;

defined('C5_EXECUTE') or die("Access Denied.");

abstract class AbstractTreeJsonFormatter implements \JsonSerializable
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var \PortlandLabs\Concrete5\MigrationTool\Entity\Import\ObjectCollection
     */
    protected $collection;

    /**
     * @var \PortlandLabs\Concrete5\MigrationTool\Entity\Import\Batch	

     */
    protected $batch;

    /**
     * @var \PortlandLabs\Concrete5\MigrationTool\Batch\Validator\ValidatorInterface	

     */
    protected $validator;

    public function __construct(ObjectCollection $collection)
    {
        $em = \Package::getByHandle('migration_tool')->getEntityManager();
        $r = $em->getRepository('\PortlandLabs\Concrete5\MigrationTool\Entity\Import\Batch');
        $this->collection = $collection;
        $this->batch = $r->findFromCollection($collection);
        $this->validator = $collection->getRecordValidator($this->batch);
        $this->entityManager = $em;
    }

    protected function getValidationMessages($object)
    {
        $subject = new BatchObjectValidatorSubject($this->batch, $object);
        $result = $this->validator->validate($subject);
        return $result->getMessages();
    }

    protected function addMessagesNode(\stdClass $node, MessageCollection $messages)
    {
        if ($messages->count()) {
            $messageHolderNode = new \stdClass();
            $messageHolderNode->icon = $node->statusClass;
            $messageHolderNode->title = t('Errors');
            $messageHolderNode->children = array();
            foreach ($messages as $m) {
                $messageNode = new \stdClass();
                $messageNode->icon = $m->getFormatter()->getIconClass();
                $messageNode->title = $m->getFormatter()->output();
                $messageHolderNode->children[] = $messageNode;
            }
            $node->children[] = $messageHolderNode;
        }
    }
}
