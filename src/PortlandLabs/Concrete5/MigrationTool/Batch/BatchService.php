<?php
namespace PortlandLabs\Concrete5\MigrationTool\Batch;

use Concrete\Core\Application\Application;
use Concrete\Core\Entity\Site\Site;
use Concrete\Core\File\Filesystem;
use Concrete\Core\Package\PackageService;
use Concrete\Core\Page\Page;
use Concrete\Core\Page\Single;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\Batch;

class BatchService
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var \Concrete\Core\Application\Application
     */
    protected $application;

    /**
     * @var \Concrete\Core\File\Filesystem
     */
    protected $filesystem;

    public function __construct(Application $application, EntityManagerInterface $entityManager, Filesystem $filesystem)
    {
        $this->application = $application;
        $this->entityManager = $entityManager;
        $this->filesystem = $filesystem;
    }

    public function deleteBatch(Batch $batch): void
    {
        foreach ($batch->getObjectCollections() as $collection) {
            $this->entityManager->remove($collection);
        }
        $batch->setObjectCollections(new ArrayCollection());
        foreach ($batch->getTargetItems() as $targetItem) {
            $targetItem->setBatch(null);
            $this->entityManager->remove($targetItem);
        }
        $this->entityManager->flush();
        $this->entityManager->remove($batch);
        $this->entityManager->flush();
    }

    public function addBatch(string $name, ?Site $site = null, bool $publishToSitemap = false): Batch
    {
        $batch = new Batch();
        $batch->setName($name);
        if ($site === null) {
            $site = $this->application->make('site')->getDefault();
        }
        $batch->setSite($site);
        $batch->setPublishToSitemap($publishToSitemap);
        $batch->setFileFolderID($this->filesystem->getRootFolder()->getTreeNodeID());
        $this->entityManager->persist($batch);
        $this->entityManager->flush();

        $this->createImportNode($site);

        return $batch;
    }

    public function createImportNode(Site $site)
    {
        $batches = Page::getByPath('/!import_batches', 'RECENT', $site);
        if (!$batches || $batches->isError()) {
            $c = Single::add('/!import_batches', $this->application->make(PackageService::class)->getByHandle('migration_tool'), true);
            $c->update(['cName' => 'Import Batches']);
            $c->setOverrideTemplatePermissions(1);
            $c->setAttribute('icon_dashboard', 'fa fa-cubes');
        }
    }
}
