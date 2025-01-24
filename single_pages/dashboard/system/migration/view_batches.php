<?php

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var Concrete\Core\Form\Service\Form $form
 * @var Concrete\Core\Validation\CSRF\Token $token
 * @var Concrete\Core\Page\View\PageView $view
 *
 * @var string $batchType
 * @var PortlandLabs\Concrete5\MigrationTool\Entity\Import\Batch[]|PortlandLabs\Concrete5\MigrationTool\Entity\Export\Batch[] $batches
 * @var Concrete\Core\Entity\Site\Site[] $sites
 * @var Concrete\Core\Localization\Service\Date $dh
 */
output_vars(get_defined_vars());
?>
<div class="ccm-dashboard-header-buttons">
    <a href="javascript:void(0)" data-dialog="add-batch" class="btn btn-primary"><?= t('Add Batch') ?></a>
</div>

<?php
if ($batches !== []) {
    ?>
    <table class="table">
        <thead>
            <tr>
                <th><?= t('Batch') ?></th>
                <th><?= t('Name') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($batches as $batch) {
                ?>
                <tr>
                    <td style="white-space: nowrap"><a href="<?= $view->action('view_batch', $batch->getID()) ?>"><?= $dh->formatDateTime($batch->getDate(), true) ?></a></td>
                    <td width="100%"><?= h($batch->getName()) ?></td>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>
    <?php
} else {
    switch ($batchType) {
        case 'import':
            echo '<p>', t('You have not created any import batches. Create a batch and add content records to it.'), '</p>';
            break;
        case 'export':
            echo '<p>', t('You have not created any export batches.'), '</p>';
            break;
    }
}
?>
<div style="display: none">
    <div id="ccm-dialog-add-batch" class="ccm-ui">
        <form method="post" action="<?= $view->action('add_batch') ?>" enctype="multipart/form-data">
            <?= $token->output('add_batch') ?>
            <div class="form-group">
                <?= $form->label('date', t('Date')) ?>
                <?= $form->text('date', $dh->formatDateTime('now', true), ['disabled' => 'disabled']) ?>
            </div>
            <?php
            if (count($sites) > 1) {
                ?>
                <div class="form-group">
                    <?= $form->label('siteID', t('Site')) ?>
                    <select name="siteID" class="form-control">
                        <?php
                        foreach ($sites as $site) {
                            ?>
                            <option value="<?= $site->getSiteID() ?>"><?= h($site->getSiteName()) ?></option>
                            <?php
                        }
                        ?>
                    </select>
                </div>
                <?php
            }
            ?>
            <div class="form-group">
                <?= $form->label('name', t('Name')) ?>
                <?= $form->text('name', '') ?>
            </div>

            <?php
            if ($batchType == 'import') {
                ?>
                <fieldset>
                    <legend><?= t('Advanced') ?></legend>
                    <div class="form-group">
                        <?= $form->label('mappingFile', t('Provide Mapping Presets')) ?>
                        <?= $form->file('mappingFile') ?>
                    </div>
                </fieldset>
                <?php
            }
            ?>
        </form>
        <div class="dialog-buttons">
            <button class="btn btn-secondary float-start" onclick="jQuery.fn.dialog.closeTop()"><?= t('Cancel') ?></button>
            <button class="btn btn-primary float-end" onclick="$('#ccm-dialog-add-batch form').submit()"><?= t('Add Batch') ?></button>
        </div>
    </div>
</div>

<script>
$(function() {
    $('a[data-dialog=add-batch]').on('click', function() {
        jQuery.fn.dialog.open({
            element: '#ccm-dialog-add-batch',
            modal: true,
            width: 320,
            title: <?=json_encode(t('Add Batch')) ?>,
            height: 'auto',
        });
    });
});
</script>
