<?php
namespace Concrete\Package\MigrationTool\Controller\SinglePage\Dashboard\System\Migration\Import\Settings;

use Concrete\Package\MigrationTool\Controller\Element\Dashboard\Batches\Settings\Header;
use Concrete\Package\MigrationTool\Page\Controller\DashboardMigrationSettingsController;
use PortlandLabs\Concrete5\MigrationTool\Batch\ContentMapper\Exporter;
use PortlandLabs\Concrete5\MigrationTool\Batch\ContentMapper\PresetManager;
use Symfony\Component\HttpFoundation\Response;

class Basics extends DashboardMigrationSettingsController
{
    public function view($id = null): ?Response
    {
        $batch = $this->getBatch($id);
        if ($batch === null) {
            return $this->buildRedirect('/dashboard/system/migration');
        }
        $this->set('pageTitle', t('Settings'));
        $this->set('headerMenu', new Header($batch));
        $presetManager = new PresetManager($this->entityManager);
        $this->set('batch', $batch);
        $this->set('presetMappings', $presetManager->getPresets($batch));
        $this->set('sites', $this->app->make('site')->getList());
        $this->set('dh', $this->app->make('helper/date'));

        return null;
    }

    public function save_batch_settings(): ?Response
    {
        if (!$this->token->validate('save_batch_settings')) {
            $this->error->add($this->token->getErrorMessage());
        }
        $batch = $this->getBatch($this->request->request->get('id'));
        if ($batch === null) {
            $this->error->add(t('Invalid batch.'));
        }
        if (empty($_FILES['mappingFile']['tmp_name'])) {
            $importer = null;
        } else {
            $importer = new \PortlandLabs\Concrete5\MigrationTool\Batch\ContentMapper\Importer();
            $importer->validateUploadedFile($_FILES['mappingFile'], $this->error);
        }
        if ($this->error->has()) {
            return $this->view($this->request->request->get('id'));
        }
        if ($this->request->request->get('download_mappings')) {
            $exporter = new Exporter($batch);
            $response = new Response(
                $exporter->getElement()->asXML(),
                Response::HTTP_OK,
                [
                    'Content-Type' => 'text/xml; charset=' . APP_CHARSET,
                    'Content-Disposition' => 'attachment; filename=mappings.xml',
                ]
            );

            return $response;
        }
        if ($this->request->request->get('delete_mapping_presets')) {
            $presetManager = new PresetManager($this->entityManager);
            $presetManager->clearPresets($batch);
            $this->flash('success', t('Batch presets removed successfully.'));

            return $this->buildRedirect(['/dashboard/system/migration/import/settings/basics', $batch->getId()]);
        }
        $batch->setName($this->request->request->get('name'));
        $site = null;
        if ($this->request->request->has('siteID')) {
            $site = $this->app->make('site')->getByID($this->request->request->get('siteID'));
        }
        if (!$site) {
            $site = $this->app->make('site')->getDefault();
        }
        $batch->setSite($site);
        $batch->setPublishToSitemap($this->request->request->getBoolean('publishToSitemap'));
        if ($importer !== null) {
            $mappings = $importer->getMappings($_FILES['mappingFile']['tmp_name']);
            $presetManager = new PresetManager($this->entityManager);
            $presetManager->clearPresets($batch);
            $presetManager->savePresets($batch, $mappings);
            $presetManager->clearBatchMappings($batch);
            $this->flash('success', t('Batch updated successfully. Since you uploaded presets, existing mappings were removed. Please rescan the batch.'));
        } else {
            $this->flash('success', t('Batch updated successfully.'));
        }
        $this->entityManager->persist($batch);
        $this->entityManager->flush();

        return $this->buildRedirect(['/dashboard/system/migration/import', 'view_batch', $batch->getId()]);
    }
}
