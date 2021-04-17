<?php
namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Block;

use Concrete\Core\Sharing\ShareThisPage\Service;
use Concrete\Core\Sharing\SocialNetwork\Link;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\Batch;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\BlockValue\BlockValue;
use Concrete\Core\Page\Page;

defined('C5_EXECUTE') or die("Access Denied.");

class ShareThisPagePublisher implements PublisherInterface
{
    public function publish(Batch $batch, $bt, Page $page, $area, BlockValue $value)
    {
        $data = array();
        $data['service'] = array();
        $records = $value->getRecords();
        foreach ($records as $record) {
            $value = $record->getData();
            $value = $value['service']; // because it comes out as an array
            $service = Service::getByHandle($value);
            if ($service) {
                $data['service'][] = $service->getHandle();
            }
        }

        $b = $page->addBlock($bt, $area, $data);

        return $b;
    }
}
