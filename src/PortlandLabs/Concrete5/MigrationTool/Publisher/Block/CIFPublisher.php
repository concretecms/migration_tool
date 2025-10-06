<?php
namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Block;

use Concrete\Core\Error\UserMessageException;
use Concrete\Core\Page\Page;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\Batch;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\BlockValue\BlockValue;
use SimpleXMLElement;

defined('C5_EXECUTE') or die("Access Denied.");

class CIFPublisher implements PublisherInterface
{
    public function publish(Batch $batch, $bt, Page $page, $area, BlockValue $value)
    {
        $btc = $bt->getController();
        $xml = $value->getValue();
        $bx = simplexml_load_string($xml);
        if (!$bx instanceof SimpleXMLElement) {
            $message = t('Invalid XML found:') . "\n";
            if (!is_string($xml)) {
                $message .= t('Expected a string, got %s', gettype($xml));
            } elseif ($xml === '') {
                $message .= t('Empty string');
            } else {
                $message .= $xml;
            }
            throw new UserMessageException($message);
        }

        return $btc->import($page, $area, $bx);
    }
}
