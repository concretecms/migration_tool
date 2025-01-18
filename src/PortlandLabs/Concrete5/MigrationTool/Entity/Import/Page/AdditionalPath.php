<?php

declare(strict_types=1);


namespace PortlandLabs\Concrete5\MigrationTool\Entity\Import\Page;

use PortlandLabs\Concrete5\MigrationTool\Entity\Import\Page;

/**
 * @\Doctrine\ORM\Mapping\Entity
 * @\Doctrine\ORM\Mapping\Table(name="MigrationImportPageAdditionalPath")
 */
class AdditionalPath
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
     * @\Doctrine\ORM\Mapping\ManyToOne(targetEntity="\PortlandLabs\Concrete5\MigrationTool\Entity\Import\Page", cascade={"all"}, inversedBy="additionalPaths")
     *
     * @var \PortlandLabs\Concrete5\MigrationTool\Entity\Import\Page
     */
    protected $page;

    /**
     * @\Doctrine\ORM\Mapping\Column(type="text", nullable=false)
     *
     * @var string
     */
    protected $path = '';

    public function getID(): ?int
    {
        return $this->id;
    }

    public function getPage(): Page
    {
        return $this->page;
    }

    public function setPage(Page $value): self
    {
        $this->page = $value;

        return $this;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return $this
     */
    public function setPath(string $value): self
    {
        $this->path = $value;

        return $this;
    }
}
