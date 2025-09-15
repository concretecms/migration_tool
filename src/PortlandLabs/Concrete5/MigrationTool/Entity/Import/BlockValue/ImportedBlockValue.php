<?php
namespace PortlandLabs\Concrete5\MigrationTool\Entity\Import\BlockValue;

use Doctrine\ORM\Mapping as ORM;
use PortlandLabs\Concrete5\MigrationTool\Batch\Formatter\Block\ImportedFormatter;
use PortlandLabs\Concrete5\MigrationTool\Inspector\Block\CIFInspector;
use PortlandLabs\Concrete5\MigrationTool\Publisher\Block\CIFPublisher;

/**
 * @ORM\Table(name="MigrationImportImportedBlockValues")
 * @ORM\Entity
 */
class ImportedBlockValue extends BlockValue
{
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $originalValue;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $value;

    public function getOriginalValue(): ?string
    {
        return $this->originalValue;
    }

    /**
     * @return $this
     */
    public function setOriginal(?string $value): self
    {
        $this->originalValue = $value;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @return $this
     */
    public function setValue(?string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getFormatter()
    {
        return new ImportedFormatter($this);
    }

    public function getPublisher()
    {
        return new CIFPublisher();
    }

    public function getInspector()
    {
        return new CIFInspector($this);
    }
}
