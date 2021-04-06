<?php
namespace PortlandLabs\Concrete5\MigrationTool\Entity\Import;

use Concrete\Core\File\FileList;
use Concrete\Core\File\Set\Set;
use Doctrine\Common\Collections\ArrayCollection;
use PortlandLabs\Concrete5\MigrationTool\Batch\BatchInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="MigrationImportBatchProcesses")
 */
class BatchProcess
{

    const TYPE_SCAN_CONTENT_TYPES = 'S';
    const TYPE_PUBLISH_BATCH = 'P';

    /**
     * @ORM\Id @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $type;

    /**
     * @ORM\OneToOne(targetEntity="\Concrete\Core\Entity\Command\Process", cascade={"remove"})
     **/
    protected $process;

    /**
     * @ORM\ManyToOne(targetEntity="Batch", inversedBy="batch_processes")
     **/
    protected $batch;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type): void
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getProcess()
    {
        return $this->process;
    }

    /**
     * @param mixed $process
     */
    public function setProcess($process): void
    {
        $this->process = $process;
    }

    /**
     * @return mixed
     */
    public function getBatch()
    {
        return $this->batch;
    }

    /**
     * @param mixed $batch
     */
    public function setBatch($batch): void
    {
        $this->batch = $batch;
    }


}
