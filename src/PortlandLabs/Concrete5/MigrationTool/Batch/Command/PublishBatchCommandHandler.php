<?php

namespace PortlandLabs\Concrete5\MigrationTool\Batch\Command;

use Concrete\Core\Application\Application;
use Concrete\Core\Command\Batch\Batch as BatchBuilder;
use Concrete\Core\Command\Process\ProcessFactory;
use Concrete\Core\User\User;
use Doctrine\ORM\EntityManager;
use PortlandLabs\Concrete5\MigrationTool\Batch\BatchService;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\Batch;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\BatchProcess;
use PortlandLabs\Concrete5\MigrationTool\Publisher\Logger\Logger;
use Symfony\Component\Messenger\MessageBusInterface;

class PublishBatchCommandHandler
{


    /**
     * @var Application
     */
    protected $app;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var BatchService
     */
    protected $batchService;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var ProcessFactory
     */
    protected $processFactory;

    public function __construct(Logger $logger, BatchService $batchService, Application $app, EntityManager $entityManager, ProcessFactory $processFactory)
    {
        $this->app = $app;
        $this->batchService = $batchService;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->processFactory = $processFactory;
    }

    public function __invoke(PublishBatchCommand $command)
    {
        $r = $this->entityManager->getRepository(Batch::class);
        $batch = $r->findOneById($command->getBatchId());

        $u = $this->app->make(User::class);
        $user = $u->isRegistered() ? $u->getUserInfoObject()->getEntityObject() : null;

        $this->batchService->createImportNode($batch->getSite());
        $this->logger->openLog($batch, $user);

        $publishers = $this->app->make('migration/manager/publisher');
        foreach ($publishers->getDrivers() as $driver) {
            foreach ($driver->getPublisherCommands($batch, $this->logger) as $command) {
                $commands[] = $command;
            }
        }

        $concreteBatch = BatchBuilder::create(t('Publish Batch'), $commands);
        $process = $this->processFactory->createWithBatch($concreteBatch);
        $batchProcess = new BatchProcess();
        $batchProcess->setBatch($batch);
        $batchProcess->setProcess($process);
        $batchProcess->setType(BatchProcess::TYPE_PUBLISH_BATCH);
        $this->entityManager->persist($batchProcess);
        $this->entityManager->flush();
    }


}