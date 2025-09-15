<?php
namespace PortlandLabs\Concrete5\MigrationTool\Publisher\AttributeKeyCategory;

use Concrete\Core\Attribute\Key\CollectionKey;
use Concrete\Core\Attribute\Key\Category;
use Concrete\Core\Package\Package;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\AttributeKey\AttributeKey;

defined('C5_EXECUTE') or die("Access Denied.");

class CollectionPublisher implements PublisherInterface
{
    public function publish(AttributeKey $ak, ?Package $pkg = null)
    {
        $key = new \Concrete\Core\Entity\Attribute\Key\PageKey();
        $key->setAttributeKeyHandle($ak->getHandle());
        $key->setAttributeKeyName($ak->getName());
        $key->setIsAttributeKeyInternal($ak->getIsInternal());
        $key->setIsAttributeKeyContentIndexed($ak->getIsIndexed());
        $key->setIsAttributeKeySearchable($ak->getIsSearchable());
        return $key;
    }
}
