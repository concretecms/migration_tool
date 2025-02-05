<?php
namespace PortlandLabs\Concrete5\MigrationTool\Entity\Import;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use PortlandLabs\Concrete5\MigrationTool\Batch\Validator\Attribute\ValidatableAttributesInterface;
use PortlandLabs\Concrete5\MigrationTool\Publisher\Logger\LoggableInterface;
use PortlandLabs\Concrete5\MigrationTool\Publisher\PublishableInterface;
use PortlandLabs\Concrete5\MigrationTool\Publisher\Validator\PageValidator;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="MigrationImportPages")
 */
class Page implements PublishableInterface, ValidatableAttributesInterface, LoggableInterface
{
    public const KIND_REGULAR_PAGE = 'page';

    public const KIND_ALIAS = 'alias';

    public const KIND_EXTERNAL_LINK = 'external_link';

    /**
     * @ORM\Id @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    protected $id;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $original_path;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $batch_path;

    /**
     * @ORM\Column(type="string")
     */
    protected $public_date = '';

    /**
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $filename;

    /**
     * @ORM\Column(type="string")
     */
    protected $type = '';

    /**
     * @ORM\Column(type="string")
     */
    protected $template = '';

    /**
     * @ORM\Column(type="string")
     */
    protected $user = '';

    /**
     * @ORM\Column(type="text")
     */
    protected $description = '';

    /**
     * @ORM\Column(type="integer")
     */
    protected $position;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $package;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $is_at_root = false;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $is_global = false;

    /**
     * @ORM\Column(type="string", nullable=false, length=30)
     *
     * @var string
     */
    protected $kind = self::KIND_REGULAR_PAGE;

    /**
     * For aliases and external links.
     *
     * @ORM\Column(type="text", nullable=false)
     *
     * @var string
     */
    protected $target = '';

    /**
     * For external links.
     *
     * @ORM\Column(type="boolean", nullable=false)
     *
     * @var bool
     */
    protected $newWindow = false;

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     *
     * @var array|null
     */
    protected $localeRoot = null;

    /**
     * @ORM\OneToMany(targetEntity="\PortlandLabs\Concrete5\MigrationTool\Entity\Import\PageAttribute", mappedBy="page", cascade={"persist", "remove"})
     **/
    public $attributes;

    /**
     * @ORM\OneToMany(targetEntity="\PortlandLabs\Concrete5\MigrationTool\Entity\Import\Area", mappedBy="page", cascade={"persist", "remove"})
     * @ORM\OrderBy({"name" = "ASC"})
     **/
    public $areas;

    /**
     * @ORM\OneToMany(targetEntity="\PortlandLabs\Concrete5\MigrationTool\Entity\Import\Page\HRefLang", mappedBy="page", cascade={"persist", "remove"}, orphanRemoval=true)
     *
     * @var \Doctrine\Common\Collections\Collection<\PortlandLabs\Concrete5\MigrationTool\Entity\Import\Page\HRefLang>
     **/
    protected $hrefLangs;

    /**
     * @ORM\OneToMany(targetEntity="\PortlandLabs\Concrete5\MigrationTool\Entity\Import\Page\AdditionalPath", mappedBy="page", cascade={"persist", "remove"}, orphanRemoval=true)
     *
     * @var \Doctrine\Common\Collections\Collection<\PortlandLabs\Concrete5\MigrationTool\Entity\Import\Page\AdditionalPath>
     */
    protected $additionalPaths;

    /**
     * @ORM\ManyToOne(targetEntity="\PortlandLabs\Concrete5\MigrationTool\Entity\Import\PageObjectCollection")
     **/
    protected $collection;

    /**
     * @var bool
     */
    protected $normalizePath = true;

    /**
     * @return bool
     */
    public function canNormalizePath()
    {
        return $this->normalizePath;
    }

    /**
     * @param bool $normalizePath
     */
    public function setNormalizePath($normalizePath)
    {
        $this->normalizePath = $normalizePath;
    }

    public function __construct()
    {
        $this->attributes = new ArrayCollection();
        $this->areas = new ArrayCollection();
        $this->hrefLangs = new ArrayCollection();
        $this->additionalPaths = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getID()
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getOriginalPath()
    {
        return $this->original_path;
    }

    /**
     * @param string|null $path
     */
    public function setOriginalPath($path)
    {
        $this->original_path = $path;
    }

    /**
     * @return mixed
     */
    public function getBatchPath()
    {
        return $this->batch_path;
    }

    /**
     * @param string|null $batch_path
     */
    public function setBatchPath($batch_path)
    {
        $this->batch_path = $batch_path;
    }

    /**
     * @return string|null
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param string|null $filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    /**
     * @return string
     */
    public function getPublicDate()
    {
        return $this->public_date;
    }

    /**
     * @param string $public_date
     */
    public function setPublicDate($public_date)
    {
        $this->public_date = $public_date;
    }

    /**
     * @return \PortlandLabs\Concrete5\MigrationTool\Entity\Import\PageObjectCollection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * @param \PortlandLabs\Concrete5\MigrationTool\Entity\Import\PageObjectCollection $collection
     */
    public function setCollection($collection)
    {
        $this->collection = $collection;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param string $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param string $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
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
     * @return \Doctrine\Common\Collections\Collection<\PortlandLabs\Concrete5\MigrationTool\Entity\Import\PageAttribute>
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection<\PortlandLabs\Concrete5\MigrationTool\Entity\Import\PageAttribute> $attributes
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * {@inheritdoc}
     *
     * @see \PortlandLabs\Concrete5\MigrationTool\Batch\Validator\Attribute\ValidatableAttributesInterface::getAttributeValidatorDriver()
     */
    public function getAttributeValidatorDriver()
    {
        return 'page_attribute';
    }

    /**
     * @return \Doctrine\Common\Collections\Collection<\PortlandLabs\Concrete5\MigrationTool\Entity\Import\Area>
     */
    public function getAreas()
    {
        return $this->areas;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection<\PortlandLabs\Concrete5\MigrationTool\Entity\Import\Area> $areas
     */
    public function setAreas($areas)
    {
        $this->areas = $areas;
    }

    /**
     * {@inheritdoc}
     *
     * @see \PortlandLabs\Concrete5\MigrationTool\Publisher\PublishableInterface::getPublisherValidator()
     */
    public function getPublisherValidator()
    {
        return new PageValidator($this);
    }

    /**
     * @return bool
     */
    public function getIsAtRoot()
    {
        return $this->is_at_root;
    }

    /**
     * @param bool $is_at_root
     */
    public function setIsAtRoot($is_at_root)
    {
        $this->is_at_root = $is_at_root;
    }

    /**
     * @return string|null
     */
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * @param string|null $package
     */
    public function setPackage($package)
    {
        $this->package = $package;
    }

    /**
     * @return bool
     */
    public function getIsGlobal()
    {
        return $this->is_global;
    }

    /**
     * @param bool $is_global
     */
    public function setIsGlobal($is_global)
    {
        $this->is_global = $is_global;
    }

    public function getKind(): string
    {
        return $this->kind;
    }

    /**
     * @return $this
     */
    public function setKind(string $value): self
    {
        $this->kind = $value;

        return $this;
    }

    /**
     * For aliases and external links.
     */
    public function getTarget(): string
    {
        return $this->target;
    }

    /**
     * For aliases and external links.
     *
     * @return $this
     */
    public function setTarget(string $value): self
    {
        $this->target = $value;

        return $this;
    }

    /**
     * For external links.
     */
    public function isNewWindow(): bool
    {
        return $this->newWindow;
    }

    /**
     * For aliases and external links.
     *
     * @return $this
     */
    public function setNewWindow(bool $value): self
    {
        $this->newWindow = $value;

        return $this;
    }

    public function getLocaleRoot(): ?array
    {
        return empty($this->localeRoot) ? null : [
            'language' => $this->localeRoot[0],
            'country' => $this->localeRoot[1] ?? '',
        ];
    }

    /**
     * @return $this
     */
    public function setLocaleRoot(string $language, string $country): self
    {
        $this->localeRoot = $language === '' ? null : [$language, $country];

        return $this;
    }

    /**
     * @return $this
     */
    public function clearLocaleRoot(): self
    {
        $this->localeRoot = null;

        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection<\PortlandLabs\Concrete5\MigrationTool\Entity\Import\Page\HRefLang>
     */
    public function getHRefLangs(): Collection
    {
        return $this->hrefLangs;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection<\PortlandLabs\Concrete5\MigrationTool\Entity\Import\Page\AdditionalPath>
     */
    public function getAdditionalPaths(): Collection
    {
        return $this->additionalPaths;
    }

    /**
     * {@inheritdoc}
     *
     * @see \PortlandLabs\Concrete5\MigrationTool\Publisher\Logger\LoggableInterface::createPublisherLogObject()
     */
    public function createPublisherLogObject($publishedObject = null)
    {
        $object = new \PortlandLabs\Concrete5\MigrationTool\Entity\Publisher\Log\Object\Page();
        $object->setName($this->getName());
        $object->setBatchPath($this->getBatchPath());
        $object->setOriginalPath($this->getOriginalPath());
        if (is_object($publishedObject)) {
            $object->setPublishedPageID($publishedObject->getCollectionID());
        }
        return $object;
    }
}
