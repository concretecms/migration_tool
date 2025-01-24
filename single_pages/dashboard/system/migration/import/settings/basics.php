<?php

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var Concrete\Package\MigrationTool\Controller\SinglePage\Dashboard\System\Migration\Import\Settings\Basics $controller
 * @var Concrete\Core\Page\View\PageView $view
 * @var Concrete\Core\Validation\CSRF\Token $token
 * @var Concrete\Core\Form\Service\Form $form
 *
 * @var PortlandLabs\Concrete5\MigrationTool\Entity\Import\Batch $batch
 * @var PortlandLabs\Concrete5\MigrationTool\Entity\Import\BatchPresetTargetItem[] $presetMappings
 * @var Concrete\Core\Entity\Site\Site[] $sites
 * @var Concrete\Core\Localization\Service\Date $dh
 */
?>

<form method="post" action="<?= $view->action('save_batch_settings') ?>" enctype="multipart/form-data">

    <div class="ccm-dashboard-form-actions-wrapper">
        <div class="ccm-dashboard-form-actions">
            <a href="<?= $view->url('/dashboard/system/migration/import', 'view_batch', $batch->getID()) ?>" class="btn btn-secondary float-start"><?= t('Cancel') ?></a>
            <button class="float-end btn btn-primary" type="submit"><?= t('Save') ?></button>
        </div>
    </div>

    <?= $token->output('save_batch_settings') ?>
    <?= $form->hidden('id', $batch->getID()) ?>

    <fieldset>
        <legend><?= t('Basics') ?></legend>
        <?php
        if (count($sites) > 1) {
            $options = [];
            foreach($sites as $site) {
                $options[$site->getSiteID()] = $site->getSiteName();
            }
            ?>
            <div class="form-group">
                <?= $form->label('siteID', t('Site')) ?>
                <?= $form->select('siteID', $options, $batch->getSite() ? $batch->getSite()->getSiteID() : '') ?>
            </div>
            <?php
        }
        ?>
        <div class="form-group">
            <?= $form->label('name', t('Name')) ?>
            <?= $form->text('name', $batch->getName(), ['required' => 'required']) ?>
        </div>
    </fieldset>

    <fieldset>

        <legend><?=t('Mapping Definitions')?></legend>
        <div class="form-group">
            <div><button type="submit" name="download_mappings" value="1" class="btn btn-secondary btn-sm"><?= t('Download Current Definitions') ?></button></div>
            <div class="form-text text-muted"><?=t('Downloads all the current mappings as an XML file. This file can then be reused across multiple batches to save time.')?></div>
        </div>

        <div class="form-group">
            <label class="control-label"><?= t('Upload Mapping File') ?></label>
            <?php
            if ($presetMappings !== []) {
                ?>
                <div class="alert alert-info">
                    <?= t2('You have uploaded a preset mapping file containing %s preset', 'You have uploaded a preset mapping file containing %s presets', count($presetMappings)) ?>
                    <button class="btn btn-xs btn-default pull-right" type="submit" name="delete_mapping_presets" value="1"><?= t('Clear Presets') ?></button>
                </div>
                <?php
            } else {
                ?>
                <?= $form->file('mappingFile') ?>
                <?php
            }
            ?>
        </div>

        <?php
        View::element('import/batch/publish_to_sitemap', ['batch' => $batch, 'form' => $form], 'migration_tool')
        ?>
    </fieldset>

</form>
