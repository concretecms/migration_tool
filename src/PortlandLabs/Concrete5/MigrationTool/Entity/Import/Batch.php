<?php
namespace PortlandLabs\Concrete5\MigrationTool\Entity\Import;

use Concrete\Core\File\FileList;
use Concrete\Core\File\Set\Set;
use Doctrine\Common\Collections\ArrayCollection;
use PortlandLabs\Concrete5\MigrationTool\Batch\BatchInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="\PortlandLabs\Concrete5\MigrationTool\Entity\Import\BatchRepository")
 * @ORM\Table(name="MigrationImportBatches")
 */
class Batch implements BatchInterface
{
    /**
     * @ORM\Id @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    protected $id;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $date;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $name;

    /**
     * @ORM\ManyToOne(targetEntity="\Concrete\Core\Entity\Site\Site")
     * @ORM\JoinColumn(name="siteID", referencedColumnName="siteID", onDelete="CASCADE")
     **/
    protected $site;

    /**
     * @ORM\Column(type="text")
     */
    protected $file_folder_id = 0;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    protected $publishToSitemap = true;

    /**
     * @ORM\ManyToMany(targetEntity="ObjectCollection", cascade={"persist", "remove"}))
     * @ORM\JoinTable(name="MigrationImportBatchObjectCollections",
     *      joinColumns={@ORM\JoinColumn(name="batch_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="collection_id", referencedColumnName="id", unique=true, onDelete="CASCADE")}
     *      )
     **/
    public $collections;

    /**
     * @ORM\OneToMany(targetEntity="\PortlandLabs\Concrete5\MigrationTool\Entity\Import\BatchTargetItem", mappedBy="batch", cascade={"persist", "remove"})
     **/
    public $target_items;

    /**
     * @ORM\OneToMany(targetEntity="\PortlandLabs\Concrete5\MigrationTool\Entity\Import\BatchPresetTargetItem", mappedBy="batch", cascade={"persist", "remove"})
     **/
    public $preset_target_items;

    /**
     * @ORM\OneToMany(targetEntity="BatchProcess", mappedBy="batch", cascade={"persist", "remove"})
     **/
    public $batch_processes;

    public function __construct()
    {
        $this->date = new \DateTime();
        $this->collections = new ArrayCollection();
        $this->target_items = new ArrayCollection();
        $this->batch_processes = new ArrayCollection();
    }

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
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param mixed $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * {@inheritdoc}
     *
     * @see \PortlandLabs\Concrete5\MigrationTool\Batch\BatchInterface::getSite()
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * @param mixed $site
     */
    public function setSite($site)
    {
        $this->site = $site;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     *
     * @see \PortlandLabs\Concrete5\MigrationTool\Batch\BatchInterface::getObjectCollections()
     *
     * @return \Doctrine\Common\Collections\Collection<\PortlandLabs\Concrete5\MigrationTool\Entity\Import\ObjectCollection>
     */
    public function getObjectCollections()
    {
        return $this->collections;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection<\PortlandLabs\Concrete5\MigrationTool\Entity\Import\ObjectCollection> $collections
     */
    public function setObjectCollections($collections)
    {
        $this->collections = $collections;
    }

    public function hasRecords()
    {
        return $this->collections->count() > 0;
    }

    public function getBatchProcesses()
    {
        return $this->batch_processes;
    }

    public function setBatchProcesses($batch_processes): void
    {
        $this->batch_processes = $batch_processes;
    }


    /**
     * Returns all pages from all page collection objects in the batch.
     */
    public function getPages()
    {
        $pages = array();
        foreach ($this->getObjectCollections() as $collection) {
            if ($collection instanceof PageObjectCollection) {
                $pages = array_merge($pages, $collection->getPages()->toArray());
            }
        }

        return $pages;
    }

    /**
     * @return mixed
     */
    public function getTargetItems()
    {
        return $this->target_items;
    }

    /**
     * {@inheritdoc}
     *
     * @see \PortlandLabs\Concrete5\MigrationTool\Batch\BatchInterface::getObjectCollection()
     */
    public function getObjectCollection($type)
    {
        foreach ($this->collections as $collection) {
            if ($collection->getType() == $type) {
                return $collection;
            }
        }
    }

    /**
     * @return mixed
     */
    public function getFileFolderID()
    {
        return $this->file_folder_id;
    }

    /**
     * @param mixed $fileFolderID
     */
    public function setFileFolderID($file_folder_id)
    {
        $this->file_folder_id = $file_folder_id;
    }

    public function isPublishToSitemap(): bool
    {
        return $this->publishToSitemap;
    }

    /**
     * @return $this
     */
    public function setPublishToSitemap(bool $value): self
    {
        $this->publishToSitemap = $value;

        return $this;
    }
    
    /**
     * @param mixed $target_items
     */
    public function setTargetItems($target_items)
    {
        $this->target_items = $target_items;
    }

    public function getFileSet()
    {
        $fs = Set::getByName($this->getID());

        return $fs;
    }

    public function __sleep()
    {
        return array('id', 'date', 'name');
    }

    public function getFiles()
    {
        $fs = $this->getFileSet();
        if (is_object($fs)) {
            $list = new FileList();
            $list->filterBySet($fs);
            $list->sortByFilenameAscending();
            $files = $list->getResults();

            return $files;
        }

        return array();
    }

    /*
     * This used to work WITHOUT This but something about refactoring into ObjectCollections made this necessary in complex DQL queries.
     */
    public function __toString()
    {
        return (string) $this->getID();
    }
}
