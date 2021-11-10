<?php
namespace Concrete\Package\MigrationTool\Controller\SinglePage\Dashboard\System\Migration;

use Concrete\Core\File\Filesystem;
use Concrete\Core\File\Importer;
use Concrete\Core\File\Set\Set;
use Concrete\Core\Command\Batch\Batch as BatchBuilder;
use Concrete\Core\Filesystem\ElementManager;
use Concrete\Core\Page\PageList;
use Concrete\Package\MigrationTool\Page\Controller\DashboardPageController;
use Doctrine\Common\Collections\ArrayCollection;
use PortlandLabs\Concrete5\MigrationTool\Batch\BatchService;
use PortlandLabs\Concrete5\MigrationTool\Batch\Command\DeleteBatchProcessesCommand;
use PortlandLabs\Concrete5\MigrationTool\Batch\Command\MapContentTypesBatchProcessFactory;
use PortlandLabs\Concrete5\MigrationTool\Batch\Command\MapContentTypesCommand;
use PortlandLabs\Concrete5\MigrationTool\Batch\Command\NormalizePagePathsCommand;
use PortlandLabs\Concrete5\MigrationTool\Batch\Command\PublishBatchCommand;
use PortlandLabs\Concrete5\MigrationTool\Batch\Command\ScanContentTypesCommand;
use PortlandLabs\Concrete5\MigrationTool\Batch\Command\TransformContentTypesBatchProcessFactory;
use PortlandLabs\Concrete5\MigrationTool\Batch\ContentMapper\PresetManager;
use PortlandLabs\Concrete5\MigrationTool\Batch\Formatter\ExpressEntry\TreeEntryJsonFormatter;
use PortlandLabs\Concrete5\MigrationTool\Batch\Formatter\Page\TreePageJsonFormatter;
use PortlandLabs\Concrete5\MigrationTool\Batch\Formatter\Site\TreeSiteJsonFormatter;
use PortlandLabs\Concrete5\MigrationTool\Batch\Formatter\TreeLazyLoadItemProviderInterface;
use PortlandLabs\Concrete5\MigrationTool\Batch\Formatter\User\TreeUserJsonFormatter;
use PortlandLabs\Concrete5\MigrationTool\Batch\Validator\BatchValidatorSubject;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\Batch;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\BatchTargetItem;
use Symfony\Component\HttpFoundation\JsonResponse;

class Import extends DashboardPageController
{
    public function add_batch()
    {
        if (!$this->token->validate('add_batch')) {
            $this->error->add($this->token->getErrorMessage());
        }
        if ($_FILES['mappingFile']['tmp_name']) {
            $importer = new \PortlandLabs\Concrete5\MigrationTool\Batch\ContentMapper\Importer();
            $importer->validateUploadedFile($_FILES['mappingFile'], $this->error);
        }
        if (!$this->error->has()) {
            $service = $this->app->make(BatchService::class);
            $site = null;
            if ($this->request->request->has('siteID')) {
                $site = $this->app->make('site')->getByID($this->request->request->get('siteID'));
            }
            $batch = $service->addBatch($this->request->request->get('name'), $site);
            if ($_FILES['mappingFile']['tmp_name']) {
                $importer = new \PortlandLabs\Concrete5\MigrationTool\Batch\ContentMapper\Importer();
                $mappings = $importer->getMappings($_FILES['mappingFile']['tmp_name']);
                $presetManager = new PresetManager($this->entityManager);
                $presetManager->savePresets($batch, $mappings);
            }
            $this->flash('success', t('Batch added successfully.'));
            $this->redirect('/dashboard/system/migration/import', 'view_batch', $batch->getId());
        }
        $this->view();
    }

    public function delete_batch()
    {
        if (!$this->token->validate('delete_batch')) {
            $this->error->add($this->token->getErrorMessage());
        }
        if (!$this->error->has()) {
            $r = $this->entityManager->getRepository('\PortlandLabs\Concrete5\MigrationTool\Entity\Import\Batch');
            $batch = $r->findOneById($this->request->request->get('id'));
            if (is_object($batch)) {
                $service = $this->app->make(BatchService::class);
                $service->deleteBatch($batch);

                $this->flash('success', t('Batch removed successfully.'));
                $this->redirect('/dashboard/system/migration');
            }
        }
        $this->view();
    }

    protected function clearBatchMappings($batch)
    {
        $r = $this->entityManager->getRepository(BatchTargetItem::class);
        foreach($r->findByBatch($batch) as $item) {
            $this->entityManager->remove($item);
        }
        $this->entityManager->flush();
    }

    protected function clearContent($batch)
    {
        $this->clearBatchMappings($batch);
        foreach ($batch->getObjectCollections() as $collection) {
            $this->entityManager->remove($collection);
        }
        $batch->setObjectCollections(new ArrayCollection());
        foreach ($batch->getTargetItems() as $targetItem) {
            $targetItem->setBatch(null);
            $this->entityManager->remove($targetItem);
        }
        $this->entityManager->flush();
    }

    public function clear_batch_mappings()
    {
        if (!$this->token->validate('clear_batch_mappings')) {
            $this->error->add($this->token->getErrorMessage());
        }
        if (!$this->error->has()) {
            $r = $this->entityManager->getRepository('\PortlandLabs\Concrete5\MigrationTool\Entity\Import\Batch');
            $batch = $r->findOneById($this->request->request->get('id'));
            if (is_object($batch)) {
                $this->clearBatchMappings($batch);
                $this->flash('success', t('Batch mappings cleared successfully. You may now rescan the batch.'));
                $this->redirect('/dashboard/system/migration/import', 'view_batch', $batch->getId());
            }
        }
        $this->view();
    }

    public function reset_processes()
    {
        if (!$this->token->validate('reset_processes')) {
            $this->error->add($this->token->getErrorMessage());
        }
        if (!$this->error->has()) {
            $r = $this->entityManager->getRepository('\PortlandLabs\Concrete5\MigrationTool\Entity\Import\Batch');
            $batch = $r->findOneById($this->request->request->get('id'));
            if (is_object($batch)) {
                $command = new DeleteBatchProcessesCommand($batch->getId());
                $this->executeCommand($command);

                $this->flash('success', t('Batch processes reset successfully.'));
                $this->redirect('/dashboard/system/migration/import', 'view_batch', $batch->getId());
            }
        }
        $this->view();
    }


    public function delete_batch_items()
    {
        if (!$this->token->validate('delete_batch_items')) {
            $this->error->add($this->token->getErrorMessage());
        }
        if (!$this->error->has()) {
            $r = $this->entityManager->getRepository('\PortlandLabs\Concrete5\MigrationTool\Entity\Import\Batch');
            $batch = $r->findOneById($this->request->request->get('id'));

            if (is_object($batch)) {
                foreach($_REQUEST['item'] as $type => $items) {
                    $existingCollection = $batch->getObjectCollection($type);
                    if (is_object($existingCollection)) {
                        foreach($items as $id) {
                            $record = $existingCollection->getRecordByID($id);
                            if ($record) {
                                $this->entityManager->remove($record);
                            }
                        }
                    }
                }
                $this->entityManager->flush();

                // clear empty object collections
                foreach ($batch->getObjectCollections() as $collection) {
                    if (count($collection->getRecords()) < 1) {
                        $this->entityManager->remove($collection);
                    }
                }

                $this->entityManager->flush();
                $this->flash('success', t('Items removed successfully.'));
                return new JsonResponse();
            }
        }
        $this->view();
    }


    public function clear_batch()
    {
        if (!$this->token->validate('clear_batch')) {
            $this->error->add($this->token->getErrorMessage());
        }
        if (!$this->error->has()) {
            $r = $this->entityManager->getRepository('\PortlandLabs\Concrete5\MigrationTool\Entity\Import\Batch');
            $batch = $r->findOneById($this->request->request->get('id'));
            if (is_object($batch)) {
                $this->clearContent($batch);
                $this->flash('success', t('Batch cleared successfully.'));
                $this->redirect('/dashboard/system/migration/import', 'view_batch', $batch->getId());
            }
        }
        $this->view();
    }

    public function delete_files()
    {
        if (!$this->token->validate('delete_files')) {
            $this->error->add($this->token->getErrorMessage());
        }
        if (!$this->error->has()) {
            $r = $this->entityManager->getRepository('\PortlandLabs\Concrete5\MigrationTool\Entity\Import\Batch');
            $batch = $r->findOneById($this->request->request->get('id'));
            if (is_object($batch)) {
                foreach ($batch->getFiles() as $f) {
                    $fp = new \Permissions($f);
                    if ($fp->canDeleteFile()) {
                        $f->delete();
                    }
                }
                $this->flash('success', t('Batch files deleted successfully.'));
                $this->redirect('/dashboard/system/migration/import', 'batch_files', $batch->getId());
            }
        }
        $this->view();
    }

    public function add_content_to_batch()
    {

        if (!$this->token->validate('add_content_to_batch')) {
            $this->error->add($this->token->getErrorMessage());
        }

        $importer = \Core::make('migration/manager/importer/parser')->driver($this->request->request('format'));

        $uploadedFile = $this->request->files->get('file');
        if ($uploadedFile === null) {
            $this->error->add(t('File not uploaded'));
        } elseif (!$uploadedFile->isValid()) {
            $this->error->add($uploadedFile->getErrorMessage());
        } else {
            $importer->validateUploadedFile($_FILES['file'], $this->error);
        }

        $r = $this->entityManager->getRepository('\PortlandLabs\Concrete5\MigrationTool\Entity\Import\Batch');
        $batch = $r->findOneById($this->request->request->get('id'));
        if (!is_object($batch)) {
            $this->error->add(t('Invalid batch.'));
        }

        if (!$this->error->has()) {
            if ($this->request->request->get('importMethod') == 'replace') {
                $this->clearContent($batch);
            }

            $importer->addContentObjectCollectionsToBatch($_FILES['file']['tmp_name'], $batch);

            $this->executeCommand(new ScanContentTypesCommand($batch->getId()));

            $this->flash('success', t('Content added. Rescanning in progress...'));

            $this->entityManager->flush();

            return new JsonResponse($batch);
        } else {
            return $this->app->make('helper/ajax')->sendError($this->error);
        }
        $this->app->shutdown();
    }

    public function rescan_batch_items()
    {
        if (!$this->token->validate('rescan_batch_items')) {
            $this->error->add($this->token->getErrorMessage());
        }
        $r = $this->entityManager->getRepository('\PortlandLabs\Concrete5\MigrationTool\Entity\Import\Batch');
        $batch = $r->findOneById($this->request->request->get('id'));
        if (!is_object($batch)) {
            $this->error->add(t('Invalid batch.'));
        }
        if (!$this->error->has()) {
            $command = new ScanContentTypesCommand($batch->getId());
            $this->executeCommand($command);
            return new JsonResponse($batch);
        }
        $this->view();
    }

    public function create_content_from_batch()
    {
        if (!$this->token->validate('create_content_from_batch')) {
            $this->error->add($this->token->getErrorMessage());
        }
        $r = $this->entityManager->getRepository('\PortlandLabs\Concrete5\MigrationTool\Entity\Import\Batch');
        $batch = $r->findOneById($this->request->request->get('id'));
        if (!is_object($batch)) {
            $this->error->add(t('Invalid batch.'));
        }
        if (!$this->error->has()) {
            $command = new PublishBatchCommand($batch->getId());
            $this->executeCommand($command);
            return new JsonResponse($batch);
        }
        $this->view();
    }

    public function view()
    {
        $r = $this->entityManager->getRepository('\PortlandLabs\Concrete5\MigrationTool\Entity\Import\Batch');
        $batches = $r->findAll(array(), array('date' => 'desc'));
        $this->set('batches', $batches);
        $this->set('batchType', 'import');
        $this->set('sites', $this->app->make('site')->getList());
        $this->set('batchEmptyMessage', t('You have not created any import batches. Create a batch and add content records to it.'));
        $this->render('/dashboard/system/migration/view_batches');
    }

    public function view_batch($id = null)
    {
        $r = $this->entityManager->getRepository('\PortlandLabs\Concrete5\MigrationTool\Entity\Import\Batch');
        $batch = $r->findOneById($id);
        if (is_object($batch)) {
            $formats = array();
            $drivers = \Core::make('migration/manager/importer/parser')->getDrivers();
            foreach ($drivers as $driver) {
                $formats[$driver->getDriver()] = $driver->getName();
            }
            $this->set('batch', $batch);
            $this->set('pageTitle', t('Import Batch'));
            $mapperDrivers = \Core::make('migration/manager/mapping')->getDrivers();
            usort($mapperDrivers, function($a, $b) {
                return strnatcasecmp($a->getMappedItemPluralName(), $b->getMappedItemPluralName());
            });
            $this->set('mapperDrivers', $mapperDrivers);
            $this->set('formats', $formats);

            $list = new PageList();
            $list->filterByPath('/dashboard/system/migration/import/settings');
            $list->ignorePermissions();
            $list->sortByDisplayOrder();
            $list->includeSystemPages();

            $settings = array();
            foreach($list->getResults() as $setting) {
                if ($setting->getCollectionHandle() != 'basics') {
                    $settings[] = $setting;
                }
            }
            $service = $this->app->make(BatchService::class);
            if ($batch->getBatchProcesses()->count()) {
                $processes = [];
                foreach($batch->getBatchProcesses() as $batchProcess) {
                    $processes[] = $batchProcess->getProcess();
                }
                $this->set('activeProcesses', $processes);
            }
            $this->set('settings', $settings);
            $this->render('/dashboard/system/migration/view_batch');
        } else {
            $this->view();
        }
    }

    public function batch_files($id = null)
    {
        $r = $this->entityManager->getRepository('\PortlandLabs\Concrete5\MigrationTool\Entity\Import\Batch');
        $batch = $r->findOneById($id);
        if (is_object($batch)) {
            $this->set('files', $batch->getFiles());
            $this->set('batch', $batch);
            $this->set('pageTitle', t('Files in Batch'));
            $this->render('/dashboard/system/migration/batch_files');
        } else {
            $this->view();
        }
    }

    public function upload_files()
    {
        $files = array();
        if ($this->token->validate('upload_files')) {
            $r = $this->entityManager->getRepository('\PortlandLabs\Concrete5\MigrationTool\Entity\Import\Batch');
            $batch = $r->findOneById($this->request->request('id'));
            if (is_object($batch)) {
                $cf = \Core::make('helper/file');
                $fp = \FilePermissions::getGlobal();
                if (isset($_FILES['file']) && (is_uploaded_file($_FILES['file']['tmp_name']))) {
                    if (!$fp->canAddFileType($cf->getExtension($_FILES['file']['name']))) {
                        throw new \Exception(Importer::getErrorMessage(Importer::E_FILE_INVALID_EXTENSION));
                    } else {
                        $ih = new Importer();
                        $filesystem = new Filesystem();
                        $folder = $filesystem->getFolder($batch->getFileFolderID());
                        if (!is_object($folder)) {
                            $folder = $filesystem->getRootFolder();
                        }

                        $filename = $_FILES['file']['name'];
                        if (preg_match("/([0-9]{12}]*)\_(.*)/", $filename, $matches)) {
                            // a prefix is already present in the filename.
                            $fvPrefix = $matches[1];
                            $fvFilename = $matches[2];
                        } else {
                            $fvPrefix = null;
                            $fvFilename = $filename;
                        }

                        $response = $ih->import($_FILES['file']['tmp_name'], $fvFilename, $folder, $fvPrefix);
                        if (!($response instanceof \Concrete\Core\Entity\File\Version)) {
                            throw new \Exception(Importer::getErrorMessage($response));
                        } else {
                            $file = $response->getFile();
                            $fs = Set::getByName($batch->getID());
                            if (!is_object($fs)) {
                                $fs = Set::createAndGetSet($batch->getID(), Set::TYPE_PRIVATE);
                            }
                            $fs->addFileToSet($file);
                            $files[] = $file;
                        }
                    }
                }
            }
        }

        $this->flash('success', t('File(s) uploaded successfully'));
        $r = new \Concrete\Core\File\EditResponse();
        $r->setFiles($files);
        $r->outputJSON();
    }

    public function find_and_replace($id = null)
    {
        $this->view_batch($id);
        $this->set('pageTitle', t('Find and Replace'));
        $this->render('/dashboard/system/migration/find_and_replace');
    }

    public function save_mapping()
    {
        if (!$this->token->validate('save_mapping')) {
            $this->error->add($this->token->getErrorMessage());
        }

        $r = $this->entityManager->getRepository('\PortlandLabs\Concrete5\MigrationTool\Entity\Import\Batch');
        $batch = $r->findOneById($this->request->request->get('id'));
        if (!is_object($batch)) {
            $this->error->add(t('Invalid batch.'));
        }


        if (!$this->error->has()) {
            $mappers = \Core::make('migration/manager/mapping');
            // First, delete all target items for this particular type, since we're going to re-map
            // them in the post below.
            $this->clearBatchMappings($batch);

            foreach($mappers->getDrivers() as $mapper) {
                $items = $mapper->getItems($batch);
                $post = $this->request->request->get('targetItem');
                $targetItemList = $mappers->createTargetItemList($batch, $mapper);

                foreach ($items as $item) {
                    $value = $post[$mapper->getHandle()][$item->getIdentifier()];
                    $targetItem = $targetItemList->getTargetItem($value);
                    $targetItem->setSourceItemIdentifier($item->getIdentifier());

                    $batchTargetItem = $mappers->createBatchTargetItem();
                    $batchTargetItem->setBatch($batch);
                    $batchTargetItem->setTargetItem($targetItem);
                    $batch->target_items->add($batchTargetItem);
                    $this->entityManager->persist($batchTargetItem);
                }
            }


            $this->entityManager->flush();

            $this->flash('message', t('Batch mappings updated. Full changes will not take effect until batch items are rescanned.'));
            $this->redirect('/dashboard/system/migration/import', 'view_batch', $batch->getId());
        }
    }

    public function validate_batch()
    {
        session_write_close();
        if (!$this->token->validate('validate_batch')) {
            $this->error->add($this->token->getErrorMessage());
        }
        if (!$this->error->has()) {
            $r = $this->entityManager->getRepository('\PortlandLabs\Concrete5\MigrationTool\Entity\Import\Batch');
            $batch = $r->findOneById($this->request->request->get('id'));
            if (is_object($batch)) {
                $validator = \Core::make('migration/batch/validator');
                $subject = new BatchValidatorSubject($batch);
                $result = $validator->validate($subject);
                $formatter = $validator->getFormatter($result);
                $data['alertclass'] = $formatter->getAlertClass();
                $data['message'] = $formatter->getCreateStatusMessage();
                $data['messages'] = $formatter->getSortedMessages();
                return new JsonResponse($data);
            }
        }
    }

    public function load_batch_collection()
    {
        session_write_close();
        $r = $this->entityManager->getRepository('\PortlandLabs\Concrete5\MigrationTool\Entity\Import\ObjectCollection');
        $collection = $r->findOneById($this->request->get('id'));
        if (is_object($collection)) {
            $formatter = $collection->getTreeFormatter();

            return new JsonResponse($formatter);
        }
    }

    public function load_batch_item_data()
    {
        session_write_close();
        $r = $this->entityManager->getRepository('\PortlandLabs\Concrete5\MigrationTool\Entity\Import\ObjectCollection');
        $collection = $r->findOneById($this->request->get('collection_id'));
        if (is_object($collection)) {
            $formatter = $collection->getTreeFormatter();
            if (!($formatter instanceof TreeLazyLoadItemProviderInterface)) {
                throw new \Exception(t('This formatter must be an instance of the TreeLazyLoadItemProviderInterface'));
            } else {
                $formatter = $formatter->getItemFormatterByID($this->request->get('id'));
                return new JsonResponse($formatter);
            }
        }
    }

    public function load_batch_page_data()
    {
        session_write_close();
        $r = $this->entityManager->getRepository('\PortlandLabs\Concrete5\MigrationTool\Entity\Import\Page');
        $page = $r->findOneById($this->request->get('id'));
        $r = $this->entityManager->getRepository(Batch::class);
        $batch = $r->findOneById($this->request->get('batch_id'));
        if ($page && $batch) {
            $formatter = new TreePageJsonFormatter($batch, $page);

            return new JsonResponse($formatter);
        }
    }

    public function load_batch_express_entry_data()
    {
        session_write_close();
        $r = $this->entityManager->getRepository('\PortlandLabs\Concrete5\MigrationTool\Entity\Import\Express\Entry');
        $page = $r->findOneById($this->request->get('id'));
        if (is_object($page)) {
            $formatter = new TreeEntryJsonFormatter($page);

            return new JsonResponse($formatter);
        }
    }


    public function load_batch_site_data()
    {
        session_write_close();
        $r = $this->entityManager->getRepository('\PortlandLabs\Concrete5\MigrationTool\Entity\Import\Site');
        $site = $r->findOneById($this->request->get('id'));
        if (is_object($site)) {
            $formatter = new TreeSiteJsonFormatter($site);

            return new JsonResponse($formatter);
        }
    }

    public function load_batch_user_data()
    {
        session_write_close();
        $r = $this->entityManager->getRepository('\PortlandLabs\Concrete5\MigrationTool\Entity\Import\User');
        $user = $r->findOneById($this->request->get('id'));
        if (is_object($user)) {
            $formatter = new TreeUserJsonFormatter($user);

            return new JsonResponse($formatter);
        }
    }

    public function map_content($id = null)
    {
        $r = $this->entityManager->getRepository('\PortlandLabs\Concrete5\MigrationTool\Entity\Import\Batch');
        $batch = $r->findOneById($id);
        $mappers = \Core::make('migration/manager/mapping');
        if (is_object($batch)) {
            $this->set('batch', $batch);
            $this->set('pageTitle', t('Map Content'));
            $this->set('mappers', $mappers);
            $this->render('/dashboard/system/migration/map_content');
        } else {
            $this->view();
        }
    }
}
