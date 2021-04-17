<?php
namespace PortlandLabs\Concrete5\MigrationTool\Importer\CIF\Block;

use PortlandLabs\Concrete5\MigrationTool\Entity\Import\BlockValue\StandardBlockDataRecord;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\BlockValue\StandardBlockValue;

defined('C5_EXECUTE') or die("Access Denied.");

class ShareThisPageImporter extends AbstractImporter
{
    public function parse(\SimpleXMLElement $node)
    {
        $value = new StandardBlockValue();
        $i = 0;
        if (!empty($node->data->service)) {
            foreach ($node->data->service as $serviceNode) {
                $service = (string) $serviceNode;
                $record = new StandardBlockDataRecord();
                $record->setTable(t('Service Record'));
                $recordData = array();
                $recordData['service'] = $service;
                $record->setData($recordData);
                $record->setPosition($i);
                $record->setValue($value);
                $value->getRecords()->add($record);
                ++$i;
            }
        }

        return $value;
    }
}
