<?php
namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler;

use Concrete\Core\Tree\Node\NodeType;
use PortlandLabs\Concrete5\MigrationTool\Batch\BatchInterface;
use PortlandLabs\Concrete5\MigrationTool\Publisher\Logger\LoggerInterface;

defined('C5_EXECUTE') or die("Access Denied.");

class CreateTreeNodeTypesCommandHandler extends AbstractHandler
{
    public function execute(BatchInterface $batch, LoggerInterface $logger)
    {
        $types = $batch->getObjectCollection('tree_node_type');

        if (!$types) {
            return;
        }

        foreach ($types->getTypes() as $type) {
            if (!$type->getPublisherValidator()->skipItem()) {
                $logger->logPublishStarted($type);

                $pkgHandle = null;
                if ($type->getPackage()) {
                    $pkgHandle = $type->getPackage()->getPackageHandle();
                }
                $tree = NodeType::add($type->getHandle(), $pkgHandle);

                $logger->logPublishComplete($type, $tree);
            } else {
                $logger->logSkipped($type);
            }
        }
    }
}
