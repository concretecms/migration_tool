<?php

namespace PortlandLabs\Concrete5\MigrationTool\Batch\Command;

use Concrete\Core\Application\Application;
use Concrete\Core\Command\Batch\Batch as BatchBuilder;
use Concrete\Core\Command\Process\ProcessFactory;
use Doctrine\ORM\EntityManager;
use PortlandLabs\Concrete5\MigrationTool\Batch\ContentMapper\EmptyMapper;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\Batch;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\BatchProcess;

class ScanContentTypesCommandHandler
{

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var Application
     */
    protected $app;

    /**
     * @var ProcessFactory
     */
    protected $processFactory;

    /**
     * @param EntityManager $entityManager
     * @param Application $app
     * @param ProcessFactory $processFactory
     */
    public function __construct(EntityManager $entityManager, Application $app, ProcessFactory $processFactory)
    {
        $this->entityManager = $entityManager;
        $this->app = $app;
        $this->processFactory = $processFactory;
    }

    public function __invoke(ScanContentTypesCommand $command)
    {
        $batch = $this->entityManager->find(Batch::class, $command->getBatchId());
        if ($batch) {
            $commands = [new NormalizePagePathsCommand($batch->getId())];

            $mappers = $this->app->make('migration/manager/mapping');
            $transformers = \Core::make('migration/manager/transforms');

            foreach ($mappers->getDrivers() as $mapper) {
                foreach ($mapper->getItems($batch) as $item) {
                    $command = new MapContentTypesCommand(
                        $batch->getID(), $mapper->getHandle(), $item->getIdentifier()
                    );
                    $commands[] = $command;
                }
            }

            foreach ($transformers->getDrivers() as $transformer) {
                try {
                    $mapper = $mappers->driver($transformer->getDriver());
                } catch (\Exception $e) {
                    // No mapper for this type.}
                    $mapper = new EmptyMapper();
                }

                $untransformed = $transformer->getUntransformedEntityObjects($mapper, $batch);
                foreach ($untransformed as $entity) {
                    $command = new TransformContentTypesCommand(
                        $batch->getID(), $entity->getID(), $mapper->getHandle(), $transformer->getDriver()
                    );
                    $commands[] = $command;
                }
            }

            $concreteBatch = BatchBuilder::create(t('Scan Content Types'), $commands);
            $process = $this->processFactory->createWithBatch($concreteBatch);
            $batchProcess = new BatchProcess();
            $batchProcess->setBatch($batch);
            $batchProcess->setProcess($process);
            $batchProcess->setType(BatchProcess::TYPE_SCAN_CONTENT_TYPES);
            $this->entityManager->persist($batchProcess);
            $this->entityManager->flush();
        }
    }
}