<?php

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var Concrete\Core\Page\View\PageView $view
 *
 * @var Concrete\Core\Form\Service\Form $form
 * @var PortlandLabs\Concrete5\MigrationTool\Entity\Import\Batch|null $batch
 */

?>
<div class="form-group">
    <?= $form->label('publishToSitemap', t('Destination of the publication')) ?>
    <div class="radio">
        <label class="form-check-label">
            <?= $form->radio('publishToSitemap', '0', $batch === null || !$batch->isPublishToSitemap() ? '0' : '1') ?>
            <b><?= t('Publish to %s', 'Import Batches') ?></b>
        </label>
        <div class="small text-muted">
            <?= t('Use this method to publish the imported pages under the %s section of the sitemap.', '<code>Import Batches</code>') ?>
            <div><b><?= t('Pros') ?></b></div>
            <ul class="my-0">
                <li><?= t("The published pages of the website won't be affected by the import") ?></li>
            </ul>
            <div><b><?= t('Cons') ?></b></div>
            <ul class="my-0">
                <li><?= t("You'll have to manually copy/move the pages from the %s section to the final destination", '<code>Import Batches</code>') ?></li>
                <li><?= t("Import of multilingual data will be partial") ?></li>
            </ul>
        </div>
    </div>
    <div class="radio">
        <label>
            <?= $form->radio('publishToSitemap', '1', $batch === null || !$batch->isPublishToSitemap() ? '0' : '1') ?>
            <b><?= t('Publish to production paths') ?></b>
        </label>
        <div class="small text-muted">
            <?= t('Use this method to publish the imported pages under the actual sitemap') ?>
            <div><b><?= t('Pros') ?></b></div>
            <ul class="my-0">
                <li><?= t("Full support for importing multilingual data") ?></li>
                <li><?= t("You won't have to manually move the pages to their final destination") ?></li>
            </ul>
            <div><b><?= t('Cons') ?></b></div>
            <ul class="my-0">
                <li><?= t("Before importing pages, you have to manually delete the pages in your sitemap (or empty their content)") ?>
            </ul>
        </div>
    </div>
</div>
