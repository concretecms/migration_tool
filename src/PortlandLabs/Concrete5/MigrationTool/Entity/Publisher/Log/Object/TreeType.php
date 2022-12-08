<?php
namespace PortlandLabs\Concrete5\MigrationTool\Entity\Publisher\Log\Object;

use PortlandLabs\Concrete5\MigrationTool\Publisher\Logger\Formatter\Object\TreeTypeFormatter;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="MigrationPublisherLogTreeTypes")
 */
class TreeType extends LoggableObject
{

    /**
     * @ORM\Column(type="string")
     */
    protected $handle;


    /**
     * @return mixed
     */
    public function getHandle()
    {
        return $this->handle;
    }

    /**
     * @param mixed $handle
     */
    public function setHandle($handle)
    {
        $this->handle = $handle;
    }


    public function getLogFormatter()
    {
        return new TreeTypeFormatter();
    }
}
