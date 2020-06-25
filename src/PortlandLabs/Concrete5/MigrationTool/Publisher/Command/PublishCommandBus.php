<?php

namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Command;

use Concrete\Core\Foundation\Command\AbstractSynchronousBus;
use PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Middleware\PublisherExceptionHandlingMiddleware;

class PublishCommandBus extends AbstractSynchronousBus
{

    public static function getHandle(): string
    {
        return 'publish';
    }

    public function getMiddleware(): array
    {
        return [
            $this->app->make(PublisherExceptionHandlingMiddleware::class)
        ];
    }

}