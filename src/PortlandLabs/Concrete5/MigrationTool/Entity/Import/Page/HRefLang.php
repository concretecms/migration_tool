<?php

declare(strict_types=1);


namespace PortlandLabs\Concrete5\MigrationTool\Entity\Import\Page;

use PortlandLabs\Concrete5\MigrationTool\Entity\Import\Page;

/**
 * @\Doctrine\ORM\Mapping\Entity
 * @\Doctrine\ORM\Mapping\Table(name="MigrationImportPageHRefLangs")
 */
class HRefLang
{
    /**
     * @\Doctrine\ORM\Mapping\Id
     * @\Doctrine\ORM\Mapping\Column(type="integer", options={"unsigned":true})
     * @\Doctrine\ORM\Mapping\GeneratedValue(strategy="AUTO")
     *
     * @var int|null
     */
    protected $id;

    /**
     * @\Doctrine\ORM\Mapping\ManyToOne(targetEntity="\PortlandLabs\Concrete5\MigrationTool\Entity\Import\Page", cascade={"all"}, inversedBy="hrefLangs")
     *
     * @var \PortlandLabs\Concrete5\MigrationTool\Entity\Import\Page
     */
    protected $page;

    /**
     * @\Doctrine\ORM\Mapping\Column(type="string", nullable=false, length=15)
     *
     * @var string
     */
    protected $localeID = '';

    /**
     * @\Doctrine\ORM\Mapping\Column(type="text", nullable=false)
     *
     * @var string
     */
    protected $pathForLocale = '';

    public function getID(): ?int
    {
        return $this->id;
    }

    public function getPage(): Page
    {
        return $this->page;
    }

    /**
     * @return $this
     */
    public function setPage(Page $value): self
    {
        $this->page = $value;

        return $this;
    }

    public function getLocaleID(): string
    {
        return $this->localeID;
    }

    /**
     * @return $this
     */
    public function setLocaleID(string $value): self
    {
        $this->localeID = $value;

        return $this;
    }

    public function getPathForLocale(): string
    {
        return $this->pathForLocale;
    }

    /**
     * @return $this
     */
    public function setPathForLocale(string $value): self
    {
        $this->pathForLocale = $value;

        return $this;
    }
}
