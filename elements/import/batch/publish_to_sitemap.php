<?php

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var Concrete\Core\Page\View\PageView $view
 *
 * @var Concrete\Core\Form\Service\Form $form
 * @var PortlandLabs\Concrete5\MigrationTool\Entity\Import\Batch|null $batch
 */

ob_start();
?>
<div class="text-start text-wrap">
    <?= t('Use this method to publish the imported pages under the %s section of the sitemap.', '<code>' . t('Import Batches') . '</code>') ?><br />
    <b><?= t('Pros') ?></b>
    <ul class="mb-0">
        <li><?= t("The published pages of the website won't be affected by the import") ?></li>
    </ul>
    <b><?= t('Cons') ?></b>
    <ul class="mb-0">
        <li><?= t("You'll have to manually copy/move the pages from the %s section to the final destination", '<code>' . t('Import Batches') . '</code>') ?></li>
        <li><?= t("Import of multilingual data will be very limited") ?></li>
    </ul>
    <br />
    <?= t(
        'In order to view %1$s, you have to go to the %2$s dashboard page and enable the %3$s option.',
        '<code>' . t('Import Batches') . '</code>',
        t('Sitemap'),
        '<code>' . t('Include System Pages in Sitemap') . '</code>'
    ) ?>
</div>
<?php
$importBatchesTooltop = ob_get_contents();
ob_end_clean();

ob_start();
?>
<div class="text-start text-wrap">
    <?= t('Use this method to publish the imported pages under the actual sitemap') ?><br />
    <b><?= t('Pros') ?></b>
    <ul class="mb-0">
        <li><?= t("Full support for importing multilingual data") ?></li>
        <li><?= t("You won't have to manually move the pages to their final destination") ?></li>
    </ul>
    <b><?= t('Cons') ?></b>
    <ul class="mb-0">
        <li><?= t("Before importing pages, you have to manually delete the pages in your sitemap (or empty their content)") ?>
    </ul>
</div>
<?php
$sitemapTooltip = ob_get_contents();
ob_end_clean();

?>
<div class="form-group">
    <?= $form->label('publishToSitemap', t('Destination of the publication')) ?>
    <div class="form-check">
        <?= $form->radio('publishToSitemap', '0', $batch === null || !$batch->isPublishToSitemap() ? '0' : '1', ['id' => 'publishToSitemap-0']) ?>
        <label class="form-check-label" for="publishToSitemap-0">
            <?= t('Publish to %s', 'Import Batches') ?>
            <i class="fas fa-question-circle launch-tooltip" title="<?= h($importBatchesTooltop) ?>" data-bs-html="true"></i>
        </label>
    </div>
    
    <div class="form-check">
        <?= $form->radio('publishToSitemap', '1', $batch === null || !$batch->isPublishToSitemap() ? '0' : '1', ['id' => 'publishToSitemap-1']) ?>
        <label class="form-check-label" for="publishToSitemap-1">
            <?= t('Publish to production paths') ?>
            <i class="fas fa-question-circle launch-tooltip" title="<?= h($sitemapTooltip) ?>" data-bs-html="true"></i>
        </label>
    </div>

    <?php
    if ($batch !== null) {
        ?>
        <div class="form-text"><?= t("Please remark that if you change this setting, you'll have to rescan the batch.") ?></div>
        <?php
    }
    ?>
</div>
