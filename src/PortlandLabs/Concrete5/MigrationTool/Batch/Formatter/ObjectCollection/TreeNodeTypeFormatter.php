<?php
namespace PortlandLabs\Concrete5\MigrationTool\Batch\Formatter\ObjectCollection;

defined('C5_EXECUTE') or die("Access Denied.");

class TreeNodeTypeFormatter extends AbstractFormatter
{
    public function getPluralDisplayName()
    {
        return t('Tree Node Types');
    }
}
