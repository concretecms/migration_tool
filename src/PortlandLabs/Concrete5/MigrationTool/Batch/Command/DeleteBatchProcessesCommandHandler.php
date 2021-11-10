<?php

namespace PortlandLabs\Concrete5\MigrationTool\Batch\Command;

use PortlandLabs\Concrete5\MigrationTool\Entity\Import\Batch;
use Doctrine\ORM\EntityManager;

class DeleteBatchProcessesCommandHandler
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function __invoke(DeleteBatchProcessesCommand $command)
    {
        $batch = $this->entityManager->getRepository(Batch::class)->findOneById($command->getBatchId());
        if ($batch) {
            $processes = $batch->getBatchProcesses();
            foreach($processes as $process) {
                $this->entityManager->remove($process);
            }
            $this->entityManager->flush();
        }
    }


}