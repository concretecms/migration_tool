<?php

namespace Concrete\Package\MigrationTool\Page\Controller;

use PortlandLabs\Concrete5\MigrationTool\Entity\Import\Batch;

class DashboardPageController extends \Concrete\Core\Page\Controller\DashboardPageController
{
    public function on_start()
    {
        set_time_limit(0);
        parent::on_start();
    }

    protected function getBatch(?string $id): ?Batch
    {
        return $id ? $this->entityManager->find(Batch::class, $id) : null;
    }
}
