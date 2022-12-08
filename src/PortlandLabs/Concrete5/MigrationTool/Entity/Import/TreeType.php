<?php
namespace PortlandLabs\Concrete5\MigrationTool\Entity\Import;

use PortlandLabs\Concrete5\MigrationTool\Publisher\Logger\LoggableInterface;
use PortlandLabs\Concrete5\MigrationTool\Publisher\PublishableInterface;
use Doctrine\ORM\Mapping as ORM;
use PortlandLabs\Concrete5\MigrationTool\Publisher\Validator\TreeTypeValidator;

/**
 * @ORM\Entity
 * @ORM\Table(name="MigrationImportTreeTypes")
 */
class TreeType implements PublishableInterface, LoggableInterface
{
    /**
     * @ORM\Id @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="\PortlandLabs\Concrete5\MigrationTool\Entity\Import\TreeTypeObjectCollection")
     **/
    protected $collection;

    /**
     * @ORM\Column(type="string")
     */
    protected $handle;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $package = null;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * @param mixed $collection
     */
    public function setCollection($collection)
    {
        $this->collection = $collection;
    }

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

    /**
     * @return mixed
     */
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * @param mixed $package
     */
    public function setPackage($package)
    {
        $this->package = $package;
    }

    public function getPublisherValidator()
    {
        return new TreeTypeValidator($this);
    }

    public function createPublisherLogObject($publishedObject = null)
    {
        $type = new \PortlandLabs\Concrete5\MigrationTool\Entity\Publisher\Log\Object\TreeType();
        $type->setHandle($this->getHandle());
        return $type;
    }

}
