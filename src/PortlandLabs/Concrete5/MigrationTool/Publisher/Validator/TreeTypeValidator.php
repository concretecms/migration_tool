<?php
namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Validator;

use Concrete\Core\Tree\TreeType;

class TreeTypeValidator extends AbstractValidator
{
    public function skipItem()
    {
        $type = TreeType::getByHandle($this->object->getHandle());
        return is_object($type);
    }
}
