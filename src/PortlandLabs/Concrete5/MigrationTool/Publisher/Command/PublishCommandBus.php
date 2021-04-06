<?php

namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Command;

use PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Middleware\PublisherExceptionHandlingMiddleware;

class PublishCommandBus
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