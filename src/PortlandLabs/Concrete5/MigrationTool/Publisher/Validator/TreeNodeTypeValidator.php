<?php
namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Validator;

use Concrete\Core\Tree\Node\NodeType;

class TreeNodeTypeValidator extends AbstractValidator
{
    public function skipItem()
    {
        $type = NodeType::getByHandle($this->object->getHandle());
        return is_object($type);
    }
}
