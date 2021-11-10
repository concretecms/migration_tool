<?php
namespace PortlandLabs\Concrete5\MigrationTool\Messenger;

use Concrete\Core\Events\EventDispatcher;
use Doctrine\ORM\EntityManager;
use PortlandLabs\Concrete5\MigrationTool\Entity\Publisher\Log\FatalErrorEntry;
use PortlandLabs\Concrete5\MigrationTool\Entity\Publisher\Log\Log;
use PortlandLabs\Concrete5\MigrationTool\Publisher\Logger\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;

class PublisherMessengerEventSubscriber implements EventSubscriberInterface
{

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var Logger
     */
    protected $batchLoggingService;

    public function __construct(EntityManager $entityManager, Logger $batchLoggingService)
    {
        $this->entityManager = $entityManager;
        $this->batchLoggingService = $batchLoggingService;
    }

    public static function getSubscribedEvents()
    {
        return [
            WorkerMessageFailedEvent::class => 'handlePublisherWorkerMessageFailedEvent',
        ];
    }

    public function handlePublisherWorkerMessageFailedEvent(WorkerMessageFailedEvent $event)
    {
        $exception = $event->getThrowable();
        $log = $this->entityManager->getRepository(Log::class)
            ->findOneById($event->getEnvelope()->getMessage()->getMessage()->getLogId());
        $this->batchLoggingService->setLog($log);

        $entry = new FatalErrorEntry();
        $entry->setMessage($exception->getMessage());
        $entry->setFilename($exception->getFile());
        $entry->setLine($exception->getLine());
        $this->batchLoggingService->logEntry($entry);
    }

}