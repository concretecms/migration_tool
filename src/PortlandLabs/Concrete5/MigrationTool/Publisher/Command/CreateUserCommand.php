<?php

namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Command;

use PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler\CreateUserCommandHandler;

class CreateUserCommand extends PublisherCommand
{

    public static function getHandler(): string
    {
        return CreateUserCommandHandler::class;
    }

    protected $userId;

    public function __construct($batchId, $logId, $userId)
    {
        $this->userId = $userId;
        parent::__construct($batchId, $logId);
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param mixed $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }



}