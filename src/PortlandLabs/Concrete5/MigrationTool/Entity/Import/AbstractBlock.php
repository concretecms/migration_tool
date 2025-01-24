<?php
namespace PortlandLabs\Concrete5\MigrationTool\Entity\Import;
use Doctrine\ORM\Mapping as ORM;
use PortlandLabs\Concrete5\MigrationTool\Publisher\Logger\LoggableInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="MigrationImportBlocks")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="value_type", type="string")
 */
class AbstractBlock implements LoggableInterface
{
    /**
     * @ORM\Id @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $type = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $name;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $custom_template;

    /**
     * @ORM\OneToOne(targetEntity="\PortlandLabs\Concrete5\MigrationTool\Entity\Import\BlockValue\BlockValue", inversedBy="block", cascade={"persist", "remove"})
     **/
    protected $block_value;

    /**
     * @ORM\OneToOne(targetEntity="StyleSet", cascade={"persist", "remove"})
     **/
    protected $style_set;

    /**
     * @ORM\Column(type="integer")
     */
    protected $position = 0;

    /**
     * @ORM\ManyToOne(targetEntity="Area")
     **/
    protected $area;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $defaults_output_identifier = null;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string|null $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return \PortlandLabs\Concrete5\MigrationTool\Entity\Import\Area
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * @param \PortlandLabs\Concrete5\MigrationTool\Entity\Import\Area $area
     */
    public function setArea($area)
    {
        $this->area = $area;
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param int $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return \PortlandLabs\Concrete5\MigrationTool\Entity\Import\BlockValue\BlockValue|null
     */
    public function getBlockValue()
    {
        return $this->block_value;
    }

    /**
     * @param \PortlandLabs\Concrete5\MigrationTool\Entity\Import\BlockValue\BlockValue|null $block_value
     */
    public function setBlockValue($block_value)
    {
        $this->block_value = $block_value;
    }

    /**
     * @return string|null
     */
    public function getDefaultsOutputIdentifier()
    {
        return $this->defaults_output_identifier;
    }

    /**
     * @param string|null $defaults_output_identifier
     */
    public function setDefaultsOutputIdentifier($defaults_output_identifier)
    {
        $this->defaults_output_identifier = $defaults_output_identifier;
    }

    /**
     * @return \PortlandLabs\Concrete5\MigrationTool\Entity\Import\StyleSet|null
     */
    public function getStyleSet()
    {
        return $this->style_set;
    }

    /**
     * @param \PortlandLabs\Concrete5\MigrationTool\Entity\Import\StyleSet|null $style_set
     */
    public function setStyleSet($style_set)
    {
        $this->style_set = $style_set;
    }

    /**
     * @return string|null
     */
    public function getCustomTemplate()
    {
        return $this->custom_template;
    }

    /**
     * @param string|null $custom_template
     */
    public function setCustomTemplate($custom_template)
    {
        $this->custom_template = $custom_template;
    }

    /**
     * {@inheritdoc}
     *
     * @see \PortlandLabs\Concrete5\MigrationTool\Publisher\Logger\LoggableInterface::createPublisherLogObject()
     */
    public function createPublisherLogObject($publishedObject = null)
    {
        $object = new \PortlandLabs\Concrete5\MigrationTool\Entity\Publisher\Log\Object\Block();
        $object->setType($this->getType());
        $object->setPage($this->getArea()->getPage()->getName());
        $object->setArea($this->getArea()->getName());
        if (is_object($publishedObject)) {
            $object->setPublishedBlockID($publishedObject->getBlockID());
        }
        return $object;
    }
}
