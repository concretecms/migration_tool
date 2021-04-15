<?php

namespace PortlandLabs\Concrete5\MigrationTool\Messenger\Middleware;

use Concrete\Core\Application\Application;
use Concrete\Core\Events\EventDispatcher;
use PortlandLabs\Concrete5\MigrationTool\Messenger\PublisherMessengerEventSubscriber;
use PortlandLabs\Concrete5\MigrationTool\Publisher\Command\PublisherCommand;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

class PublisherExceptionHandlingMiddleware implements MiddlewareInterface
{

    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @var Application
     */
    protected $app;

    public function __construct(EventDispatcher $eventDispatcher, Application $app)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->app = $app;
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $message = $envelope->getMessage();
        if ($message instanceof PublisherCommand) {

            $this->eventDispatcher->getEventDispatcher()->addSubscriber(
                $this->app->make(PublisherMessengerEventSubscriber::class)
            );

            $return = $stack->next()->handle($envelope, $stack);

            $this->eventDispatcher->getEventDispatcher()->removeSubscriber(
                $this->app->make(PublisherMessengerEventSubscriber::class)
            );

            return $return;

        } else {
            return $stack->next()->handle($envelope, $stack);
        }
    }


}
